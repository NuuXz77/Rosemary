<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Sales;
use App\Models\SaleItems;
use App\Models\Customers;
use App\Models\ProductStocks;
use App\Models\ProductStockLogs;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Checkout — Pembayaran')]
class Checkout extends Component
{
    public array $cart          = [];
    public $customer_id         = null;
    public $shift_id            = null;
    public $cashier_student_id  = null;
    public float $subtotal          = 0;
    public float $tax_amount        = 0;
    public float $discount_amount   = 0;
    public float $total_amount      = 0;
    public string $payment_method   = 'cash';
    public string $payment_status   = 'paid';
    public float $paid_amount       = 0;
    public float $change_amount     = 0;
    public string $note             = '';
    public string $guest_name       = '';
    public string $table_number     = '';
    public bool $isPinMode          = false;

    public function mount()
    {
        $cart = session('pos_checkout_cart');

        if (empty($cart)) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak ada pesanan aktif.');
            $this->redirectBack();
            return;
        }

        $this->cart             = $cart;
        $this->customer_id      = session('pos_checkout_customer_id');
        $this->shift_id         = session('pos_checkout_shift_id');
        $this->cashier_student_id = session('pos_checkout_cashier_id');
        $this->subtotal         = (float) session('pos_checkout_subtotal', 0);
        $this->tax_amount       = (float) session('pos_checkout_tax_amount', 0);
        $this->discount_amount  = (float) session('pos_checkout_discount_amount', 0);
        $this->total_amount     = (float) session('pos_checkout_total', 0);
        $this->isPinMode        = (bool) session('pos_checkout_pine_mode', false);
        $this->guest_name       = session('pos_checkout_guest_name') ?? '';
        $this->table_number     = session('pos_checkout_table_number') ?? '';
        $this->paid_amount      = $this->total_amount;
        $this->calculateChange();
    }

    protected function redirectBack(): void
    {
        $this->redirect(route('kasir.pos'), navigate: true);
    }

    public function updatedPaidAmount()
    {
        $this->calculateChange();
    }

    public function calculateChange()
    {
        $this->change_amount = max(0, $this->paid_amount - $this->total_amount);
    }

    public function setPaidAmount($amount)
    {
        $this->paid_amount = (float) $amount;
        $this->calculateChange();
    }

    public function cancelCheckout()
    {
        $this->redirectBack();
    }

    public function updatedPaymentStatus()
    {
        if ($this->payment_status === 'unpaid') {
            $this->paid_amount = 0;
            $this->calculateChange();
        } else {
            $this->paid_amount = $this->total_amount;
            $this->calculateChange();
        }
    }

    public function submitOrder()
    {
        $this->validate([
            'payment_method' => 'required|in:cash,qris,transfer',
            'payment_status' => 'required|in:paid,unpaid',
            'paid_amount'    => 'required|numeric|min:0',
        ]);

        if ($this->payment_status === 'paid' && $this->payment_method === 'cash' && $this->paid_amount < $this->total_amount) {
            $this->dispatch('show-toast', type: 'error', message: 'Uang yang dibayar kurang dari total!');
            return;
        }

        if (empty($this->cart)) {
            $this->dispatch('show-toast', type: 'error', message: 'Keranjang kosong!');
            return;
        }

        if (!$this->shift_id) {
            $this->dispatch('show-toast', type: 'error', message: 'Shift tidak terdeteksi! Silakan login ulang.');
            return;
        }

        DB::beginTransaction();
        try {
            $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . str_pad(Sales::count() + 1, 4, '0', STR_PAD_LEFT);

            $sale = Sales::create([
                'invoice_number'     => $invoiceNumber,
                'customer_id'        => $this->customer_id ?: null,
                'guest_name'         => $this->customer_id ? null : ($this->guest_name ?: null),
                'table_number'       => $this->table_number ?: null,
                'shift_id'           => $this->shift_id,
                'cashier_student_id' => $this->cashier_student_id,
                'subtotal'           => $this->subtotal,
                'tax_amount'         => $this->tax_amount,
                'discount_amount'    => $this->discount_amount,
                'total_amount'       => $this->total_amount,
                'paid_amount'        => $this->paid_amount,
                'change_amount'      => $this->change_amount,
                'payment_method'     => $this->payment_method,
                'status'             => $this->payment_status,
            ]);

            foreach ($this->cart as $item) {
                SaleItems::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['id'],
                    'qty'        => $item['qty'],
                    'price'      => $item['price'],
                    'subtotal'   => $item['subtotal'],
                ]);

                $stock = ProductStocks::where('product_id', $item['id'])->first();
                if ($stock) {
                    $stock->decrement('qty_available', $item['qty']);
                    ProductStockLogs::create([
                        'product_id'     => $item['id'],
                        'type'           => 'out',
                        'qty'            => -$item['qty'],
                        'description'    => "Penjualan #{$sale->invoice_number}",
                        'reference_type' => Sales::class,
                        'reference_id'   => $sale->id,
                        'created_by'     => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            // Clear checkout session
            session()->forget([
                'pos_checkout_cart', 'pos_checkout_customer_id', 'pos_checkout_guest_name',
                'pos_checkout_table_number', 'pos_checkout_shift_id',
                'pos_checkout_cashier_id', 'pos_checkout_total', 'pos_checkout_subtotal',
                'pos_checkout_tax_amount', 'pos_checkout_discount_amount', 'pos_checkout_pine_mode',
            ]);

            $this->dispatch('show-toast', type: 'success',
                message: "Transaksi #{$invoiceNumber} berhasil disimpan!");

            // Redirect to invoice page
            $this->redirect(route('kasir.invoice', $sale), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.sales.checkout', [
            'customers' => Customers::where('status', true)->get(),
        ]);
    }
}
