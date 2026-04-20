<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Sales;
use App\Models\SaleItems;
use App\Models\Products;
use App\Models\Categories;
use App\Models\Customers;
use App\Models\Shift;
use App\Models\Students;
use App\Models\ProductStocks;
use App\Models\ProductStockLogs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class POS extends Component
{
    #[Title('Kasir (POS)')]

    // Search & Filter
    public $search = '';
    public $barcode = '';
    public $filterCategory = '';
    public string $sortBy = 'stock_desc';

    // PIN mode flag – overridden by Kasir\POS
    public bool $pinMode = false;

    // Cart State
    public $cart = [];
    public $subtotal = 0;
    public $discount_amount = 0;
    public $total_amount = 0;

    // Transaction State
    public $customer_id = null;
    public $guest_name = '';
    public $status_order = Sales::ORDER_STATUS_TAKE_AWAY;
    public $table_number = '';
    public $shift_id = null;
    public $cashier_student_id = null;
    public $payment_method = 'cash';
    public $paid_amount = 0;
    public $change_amount = 0;

    // Receipt State
    public $lastSaleId = null;
    public $showReceipt = false;
    private static ?bool $hasQueueNumberColumn = null;

    protected $rules = [
        'shift_id' => 'required|exists:shifts,id',
        'cashier_student_id' => 'required|exists:students,id',
        'status_order' => 'required|in:Take away,Dine in',
        'table_number' => 'nullable|string|max:255',
        'payment_method' => 'required|in:cash,qris',
        'paid_amount' => 'required|numeric|min:0',
    ];

    private function resolveShiftId(): ?int
    {
        $now = now()->format('H:i:s');

        $activeShift = Shift::query()
            ->where('status', true)
            ->where(function ($query) use ($now) {
                // Shift normal (contoh 08:00-16:00)
                $query->where(function ($subQuery) use ($now) {
                    $subQuery->whereColumn('start_time', '<=', 'end_time')
                        ->whereTime('start_time', '<=', $now)
                        ->whereTime('end_time', '>=', $now);
                })
                    // Shift lintas hari (contoh 22:00-06:00)
                    ->orWhere(function ($subQuery) use ($now) {
                        $subQuery->whereColumn('start_time', '>', 'end_time')
                            ->where(function ($timeQuery) use ($now) {
                                $timeQuery->whereTime('start_time', '<=', $now)
                                    ->orWhereTime('end_time', '>=', $now);
                            });
                    });
            })
            ->orderBy('id')
            ->first();

        if ($activeShift) {
            return (int) $activeShift->id;
        }

        $fallbackShift = Shift::query()->where('status', true)->orderBy('id')->first();
        if ($fallbackShift) {
            return (int) $fallbackShift->id;
        }

        return Shift::query()->orderBy('id')->value('id');
    }

    private function salesHasQueueNumberColumn(): bool
    {
        if (self::$hasQueueNumberColumn === null) {
            self::$hasQueueNumberColumn = Schema::hasColumn('sales', 'queue_number');
        }

        return self::$hasQueueNumberColumn;
    }

    public function mount()
    {
        $this->shift_id = $this->resolveShiftId();
    }

    public function updatedPaidAmount()
    {
        $this->calculateTotals();
    }

    public function updatedStatusOrder($value)
    {
        if ($value !== Sales::ORDER_STATUS_DINE_IN) {
            $this->table_number = '';
        }
    }

    public function updatedSortBy(string $value): void
    {
        $allowed = ['stock_desc', 'stock_asc', 'price_desc', 'price_asc'];
        if (!in_array($value, $allowed, true)) {
            $this->sortBy = 'stock_desc';
        }
    }

    public function scanBarcode()
    {
        if (empty($this->barcode))
            return;

        $product = Products::where('barcode', $this->barcode)->where('status', true)->first();

        if ($product) {
            $this->addToCart($product->id);
            $this->barcode = '';
            $this->dispatch('play-beep');
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'Barcode tidak terdaftar!');
            $this->barcode = '';
        }
    }

    public function addToCart($productId)
    {
        $product = Products::with('stock')->findOrFail($productId);
        $stockAvailable = optional($product->stock)->qty_available ?? 0;

        if ($stockAvailable <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Stok produk habis!');
            return;
        }

        $existingIndex = collect($this->cart)->search(fn($item) => $item['id'] == $productId);

        if ($existingIndex !== false) {
            if ($this->cart[$existingIndex]['qty'] + 1 > $stockAvailable) {
                $this->dispatch('show-toast', type: 'error', message: 'Stok tidak mencukupi!');
                return;
            }
            $this->cart[$existingIndex]['qty']++;
            $this->cart[$existingIndex]['subtotal'] = $this->cart[$existingIndex]['qty'] * $this->cart[$existingIndex]['price'];
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'qty' => 1,
                'subtotal' => $product->price
            ];
        }

        $this->calculateTotals();
    }

    public function updateQty($index, $qty)
    {
        if ($qty <= 0) {
            $this->removeFromCart($index);
            return;
        }

        $product = Products::with('stock')->findOrFail($this->cart[$index]['id']);
        $stockAvailable = optional($product->stock)->qty_available ?? 0;

        if ($qty > $stockAvailable) {
            $this->dispatch('show-toast', type: 'error', message: 'Stok tidak mencukupi!');
            $this->cart[$index]['qty'] = $stockAvailable > 0 ? $stockAvailable : 1;
        } else {
            $this->cart[$index]['qty'] = $qty;
        }

        $this->cart[$index]['subtotal'] = $this->cart[$index]['qty'] * $this->cart[$index]['price'];
        $this->calculateTotals();
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = collect($this->cart)->sum('subtotal');
        $this->total_amount = $this->subtotal - $this->discount_amount;
        $this->change_amount = max(0, $this->paid_amount - $this->total_amount);
    }

    public function proceedToCheckout()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-toast', type: 'error', message: 'Keranjang belanja masih kosong!');
            return;
        }

        if (!$this->shift_id) {
            $this->shift_id = $this->resolveShiftId();
        }

        $this->validate([
            'status_order' => 'required|in:Take away,Dine in',
            'table_number' => 'nullable|string|max:255|required_if:status_order,Dine in',
        ]);

        session([
            'pos_checkout_cart'             => $this->cart,
            'pos_checkout_customer_id'      => $this->customer_id,
            'pos_checkout_guest_name'       => $this->customer_id
                ? null
                : (trim((string) $this->guest_name) !== '' ? trim((string) $this->guest_name) : null),
            'pos_checkout_status_order'     => $this->status_order,
            'pos_checkout_table_number'     => $this->table_number,
            'pos_checkout_shift_id'         => $this->shift_id,
            'pos_checkout_cashier_id'       => $this->cashier_student_id,
            'pos_checkout_subtotal'         => $this->subtotal,
            'pos_checkout_discount_amount'  => $this->discount_amount,
            'pos_checkout_total'            => $this->total_amount,
            'pos_checkout_pine_mode'        => $this->pinMode,
        ]);

        $this->redirect(route('kasir.checkout'), navigate: true);
    }

    public function openPayment()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-toast', type: 'error', message: 'Keranjang belanja masih kosong!');
            return;
        }

        if (!$this->shift_id) {
            $this->shift_id = $this->resolveShiftId();
        }

        $this->validate([
            'cashier_student_id' => 'required',
        ]);

        $this->paid_amount = $this->total_amount;
        $this->calculateTotals();
        $this->dispatch('open-modal', id: 'payment-modal');
    }

    public function submitOrder()
    {
        if (!$this->shift_id) {
            $this->shift_id = $this->resolveShiftId();
        }

        $this->validate();

        if (
            $this->status_order === Sales::ORDER_STATUS_DINE_IN
            && !$this->customer_id
            && trim((string) $this->guest_name) === ''
        ) {
            $this->dispatch('show-toast', type: 'error', message: 'Untuk dine in tanpa member, nama pelanggan wajib diisi.');
            return;
        }

        if ($this->payment_method === 'cash' && $this->paid_amount < $this->total_amount) {
            $this->dispatch('show-toast', type: 'error', message: 'Uang yang dibayar kurang!');
            return;
        }

        DB::beginTransaction();
        try {
            $todayDate = now()->toDateString();
            $hasQueueNumberColumn = $this->salesHasQueueNumberColumn();

            // Lock transaksi hari ini untuk mencegah nomor invoice/antrean kembar saat klik bersamaan.
            $lastSaleToday = Sales::whereDate('created_at', $todayDate)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $nextNumber = 1;
            if ($lastSaleToday && preg_match('/-(\d{4})$/', $lastSaleToday->invoice_number, $matches)) {
                $nextNumber = (int) $matches[1] + 1;
            }

            // Generate invoice: INV-YYYYMMDD-0001
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $queueNumber = null;
            if ($hasQueueNumberColumn && $this->status_order === Sales::ORDER_STATUS_TAKE_AWAY) {
                $lastQueueToday = Sales::whereDate('created_at', $todayDate)
                    ->where('status_order', Sales::ORDER_STATUS_TAKE_AWAY)
                    ->whereNotNull('queue_number')
                    ->lockForUpdate()
                    ->orderByDesc('id')
                    ->first();

                $nextQueue = 1;
                if ($lastQueueToday && preg_match('/-(\d{3})$/', (string) $lastQueueToday->queue_number, $matches)) {
                    $nextQueue = (int) $matches[1] + 1;
                }

                // Format antrean: YYYYMMDD-XXX
                $queueNumber = now()->format('Ymd') . '-' . str_pad($nextQueue, 3, '0', STR_PAD_LEFT);
            }

            $salePayload = [
                'invoice_number' => $invoiceNumber,
                'customer_id' => $this->customer_id ?: null,
                'guest_name'  => $this->customer_id ? null : ($this->guest_name ?: null),
                'status_order' => $this->status_order,
                'table_number' => $this->status_order === Sales::ORDER_STATUS_DINE_IN ? ($this->table_number ?: null) : null,
                'shift_id' => $this->shift_id,
                'cashier_student_id' => $this->cashier_student_id,
                'subtotal' => $this->subtotal,
                'tax_amount' => 0,
                'discount_amount' => $this->discount_amount,
                'total_amount' => $this->total_amount,
                'paid_amount' => $this->paid_amount,
                'change_amount' => $this->change_amount,
                'payment_method' => $this->payment_method,
                'status' => 'paid',
            ];

            if ($hasQueueNumberColumn) {
                $salePayload['queue_number'] = $queueNumber;
            }

            $sale = Sales::create($salePayload);

            foreach ($this->cart as $item) {
                SaleItems::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Deduct product stock
                $stock = ProductStocks::where('product_id', $item['id'])->first();
                if ($stock) {
                    $stock->decrement('qty_available', $item['qty']);

                    ProductStockLogs::create([
                        'product_id' => $item['id'],
                        'type' => 'out',
                        'qty' => -$item['qty'],
                        'description' => "Penjualan #{$sale->invoice_number}",
                        'reference_type' => Sales::class,
                        'reference_id' => $sale->id,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            $this->lastSaleId = $sale->id;
            $this->showReceipt = true;

            $this->dispatch('close-modal', id: 'payment-modal');
            $this->dispatch('open-modal', id: 'receipt-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Transaksi berhasil disimpan!');

            $this->reset(['cart', 'subtotal', 'discount_amount', 'total_amount', 'paid_amount', 'change_amount', 'customer_id', 'guest_name', 'table_number']);
            $this->status_order = Sales::ORDER_STATUS_TAKE_AWAY;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $stockSubquery = '(SELECT COALESCE(ps.qty_available, 0) FROM product_stocks ps WHERE ps.product_id = products.id LIMIT 1)';

        $productsQuery = Products::query()
            ->with('stock', 'category')
            ->where('status', true)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory));

        // Produk tersedia selalu di atas, lalu baru diurutkan sesuai pilihan.
        $productsQuery->orderByRaw('(CASE WHEN ' . $stockSubquery . ' > 0 THEN 0 ELSE 1 END) ASC');

        match ($this->sortBy) {
            'stock_asc' => $productsQuery->orderByRaw($stockSubquery . ' ASC'),
            'price_desc' => $productsQuery->orderBy('price', 'desc'),
            'price_asc' => $productsQuery->orderBy('price', 'asc'),
            default => $productsQuery->orderByRaw($stockSubquery . ' DESC'),
        };

        $products = $productsQuery
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.admin.sales.pos', [
            'products' => $products,
            'categories' => Categories::where('type', 'product')->get(),
            'customers' => Customers::where('status', true)->get(),
            'shifts' => Shift::where('status', true)->get(),
            'students' => Students::where('status', true)->get(),
            'lastSale' => $this->lastSaleId ? Sales::with(['items.product', 'customer', 'cashier', 'shift'])->find($this->lastSaleId) : null,
        ]);
    }
}
