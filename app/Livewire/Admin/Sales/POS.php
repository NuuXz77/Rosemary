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

    // PIN mode flag – overridden by Kasir\POS
    public bool $pinMode = false;

    // Cart State
    public $cart = [];
    public $subtotal = 0;
    public $tax_rate = 0; // 0% as default or 11%? Let's use 0 for now.
    public $tax_amount = 0;
    public $discount_amount = 0;
    public $total_amount = 0;

    // Transaction State
    public $customer_id = null;
    public $guest_name = '';
    public $shift_id = null;
    public $cashier_student_id = null;
    public $payment_method = 'cash';
    public $paid_amount = 0;
    public $change_amount = 0;

    // Receipt State
    public $lastSaleId = null;
    public $showReceipt = false;

    protected $rules = [
        'shift_id' => 'required|exists:shifts,id',
        'cashier_student_id' => 'required|exists:students,id',
        'payment_method' => 'required|in:cash,qris,transfer',
        'paid_amount' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        // Try to auto-select current shift based on time
        $now = now()->toTimeString();
        $currentShift = Shift::where('status', true)
            ->whereTime('start_time', '<=', $now)
            ->whereTime('end_time', '>=', $now)
            ->first();

        if ($currentShift) {
            $this->shift_id = $currentShift->id;
        }
    }

    public function updatedPaidAmount()
    {
        $this->calculateTotals();
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

        if ($product->stock->qty_available <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Stok produk habis!');
            return;
        }

        $existingIndex = collect($this->cart)->search(fn($item) => $item['id'] == $productId);

        if ($existingIndex !== false) {
            if ($this->cart[$existingIndex]['qty'] + 1 > $product->stock->qty_available) {
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
        if ($qty > $product->stock->qty_available) {
            $this->dispatch('show-toast', type: 'error', message: 'Stok tidak mencukupi!');
            $this->cart[$index]['qty'] = $product->stock->qty_available;
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
        $this->tax_amount = ($this->subtotal * $this->tax_rate) / 100;
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->change_amount = max(0, $this->paid_amount - $this->total_amount);
    }

    public function openConfirmModal()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-toast', type: 'error', message: 'Keranjang belanja masih kosong!');
            return;
        }
        $this->dispatch('open-modal', id: 'confirm-modal');
    }

    public function proceedToCheckout()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-toast', type: 'error', message: 'Keranjang belanja masih kosong!');
            return;
        }

        session([
            'pos_checkout_cart'             => $this->cart,
            'pos_checkout_customer_id'      => $this->customer_id,
            'pos_checkout_guest_name'       => $this->customer_id ? null : ($this->guest_name ?: 'Guest'),
            'pos_checkout_shift_id'         => $this->shift_id,
            'pos_checkout_cashier_id'       => $this->cashier_student_id,
            'pos_checkout_subtotal'         => $this->subtotal,
            'pos_checkout_tax_amount'       => $this->tax_amount,
            'pos_checkout_discount_amount'  => $this->discount_amount,
            'pos_checkout_total'            => $this->total_amount,
            'pos_checkout_pine_mode'        => $this->pinMode,
        ]);

        if ($this->pinMode) {
            $this->redirect(route('kasir.checkout'), navigate: true);
        } else {
            $this->redirect(route('sales.checkout'), navigate: true);
        }
    }

    public function openPayment()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-toast', type: 'error', message: 'Keranjang belanja masih kosong!');
            return;
        }

        $this->validate([
            'shift_id' => 'required',
            'cashier_student_id' => 'required',
        ]);

        $this->paid_amount = $this->total_amount;
        $this->calculateTotals();
        $this->dispatch('open-modal', id: 'payment-modal');
    }

    public function submitOrder()
    {
        $this->validate();

        if ($this->payment_method === 'cash' && $this->paid_amount < $this->total_amount) {
            $this->dispatch('show-toast', type: 'error', message: 'Uang yang dibayar kurang!');
            return;
        }

        DB::beginTransaction();
        try {
            // Generate Invoice Number: INV-YYYYMMDD-XXXX
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . str_pad(Sales::count() + 1, 4, '0', STR_PAD_LEFT);

            $sale = Sales::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $this->customer_id,
                'shift_id' => $this->shift_id,
                'cashier_student_id' => $this->cashier_student_id,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->tax_amount,
                'discount_amount' => $this->discount_amount,
                'total_amount' => $this->total_amount,
                'paid_amount' => $this->paid_amount,
                'change_amount' => $this->change_amount,
                'payment_method' => $this->payment_method,
                'status' => 'paid',
            ]);

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

            $this->reset(['cart', 'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'paid_amount', 'change_amount', 'customer_id']);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $products = Products::query()
            ->with('stock', 'category')
            ->where('status', true)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
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
