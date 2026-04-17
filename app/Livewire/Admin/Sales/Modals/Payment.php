<?php

namespace App\Livewire\Admin\Sales\Modals;

use App\Models\Sales;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class Payment extends Component
{
    public ?int $saleId = null;
    public string $invoiceNumber = '-';
    public string $customerName = '-';
    public float $totalAmount = 0;
    public float $paidAmountExisting = 0;
    public float $remainingAmount = 0;
    public string $payment_method = 'cash';
    public float $pay_amount = 0;
    public float $change_amount = 0;

    #[On('open-payment-sale')]
    public function loadPayment(int $id): void
    {
        if (!auth()->user()?->can('sales.edit') && !auth()->user()?->can('sales.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin memproses pembayaran.');
            return;
        }

        $sale = Sales::with('customer')->find($id);
        if (!$sale) {
            $this->dispatch('show-toast', type: 'error', message: 'Data penjualan tidak ditemukan.');
            return;
        }

        if ($sale->status !== 'unpaid') {
            $this->dispatch('show-toast', type: 'warning', message: 'Transaksi ini tidak dalam status hutang.');
            return;
        }

        $remaining = max(0, (float) $sale->total_amount - (float) $sale->paid_amount);

        $this->saleId = (int) $sale->id;
        $this->invoiceNumber = (string) $sale->invoice_number;
        $this->customerName = $sale->service_identity;
        $this->totalAmount = (float) $sale->total_amount;
        $this->paidAmountExisting = (float) $sale->paid_amount;
        $this->remainingAmount = $remaining;
        $this->payment_method = 'cash';
        $this->pay_amount = $remaining;
        $this->calculateChange();
        $this->resetValidation();

        $this->dispatch('open-modal', id: 'sales-payment-modal');
    }

    public function updatedPayAmount(): void
    {
        $this->calculateChange();
    }

    public function updatedPaymentMethod(): void
    {
        $this->calculateChange();
    }

    public function setExactAmount(): void
    {
        $this->pay_amount = $this->remainingAmount;
        $this->calculateChange();
    }

    private function calculateChange(): void
    {
        if ($this->payment_method !== 'cash') {
            $this->change_amount = 0;
            return;
        }

        $this->change_amount = max(0, (float) $this->pay_amount - (float) $this->remainingAmount);
    }

    public function pay(): void
    {
        if (!auth()->user()?->can('sales.edit') && !auth()->user()?->can('sales.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin memproses pembayaran.');
            return;
        }

        $this->validate([
            'payment_method' => 'required|in:cash,qris,transfer',
            'pay_amount' => 'required|numeric|min:0',
        ]);

        if ($this->saleId === null) {
            $this->dispatch('show-toast', type: 'error', message: 'Data pembayaran tidak valid.');
            return;
        }

        if ((float) $this->pay_amount < (float) $this->remainingAmount) {
            $this->dispatch('show-toast', type: 'error', message: 'Nominal pembayaran kurang dari sisa hutang.');
            return;
        }

        DB::beginTransaction();
        try {
            $sale = Sales::lockForUpdate()->findOrFail($this->saleId);

            if ($sale->status !== 'unpaid') {
                DB::rollBack();
                $this->dispatch('show-toast', type: 'warning', message: 'Transaksi sudah tidak berstatus hutang.');
                return;
            }

            $remaining = max(0, (float) $sale->total_amount - (float) $sale->paid_amount);
            if ((float) $this->pay_amount < $remaining) {
                DB::rollBack();
                $this->dispatch('show-toast', type: 'error', message: 'Nominal pembayaran kurang dari sisa hutang terbaru.');
                return;
            }

            $change = $this->payment_method === 'cash'
                ? max(0, (float) $this->pay_amount - $remaining)
                : 0;

            $sale->update([
                'status' => 'paid',
                'payment_method' => $this->payment_method,
                'paid_amount' => (float) $sale->total_amount,
                'change_amount' => $change,
            ]);

            DB::commit();

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Pembayaran hutang berhasil diproses.');
            $this->dispatch('sales-changed');
            $this->dispatch('open-receipt-modal', id: $sale->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.sales.modals.payment');
    }
}
