<?php

namespace App\Livewire\Admin\Sales\Modals;

use App\Models\ProductStockLogs;
use App\Models\ProductStocks;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $saleId = null;
    public string $invoiceNumber = '-';
    public string $customerName = '-';
    public string $status = '-';

    #[On('open-delete-sale')]
    public function loadDelete(int $id): void
    {
        if (!auth()->user()?->can('sales.delete') && !auth()->user()?->can('sales.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin menghapus transaksi.');
            return;
        }

        $sale = Sales::with(['customer'])->find($id);
        if (!$sale) {
            $this->dispatch('show-toast', type: 'error', message: 'Data penjualan tidak ditemukan.');
            return;
        }

        $this->saleId = (int) $sale->id;
        $this->invoiceNumber = (string) $sale->invoice_number;
        $this->customerName = $sale->service_identity;
        $this->status = (string) $sale->status;
        $this->resetValidation();

        $this->dispatch('open-modal', id: 'delete-sale-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()?->can('sales.delete') && !auth()->user()?->can('sales.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak memiliki izin menghapus transaksi.');
            return;
        }

        if ($this->saleId === null) {
            $this->dispatch('show-toast', type: 'error', message: 'Data transaksi tidak valid.');
            return;
        }

        DB::beginTransaction();
        try {
            $sale = Sales::with('items')->lockForUpdate()->findOrFail($this->saleId);

            foreach ($sale->items as $item) {
                $stock = ProductStocks::firstOrCreate(
                    ['product_id' => $item->product_id],
                    ['qty_available' => 0]
                );

                $stock->increment('qty_available', (int) $item->qty);

                ProductStockLogs::create([
                    'product_id' => $item->product_id,
                    'type' => 'in',
                    'qty' => (int) $item->qty,
                    'description' => 'Rollback stok karena penghapusan transaksi #' . $sale->invoice_number,
                    'reference_type' => Sales::class,
                    'reference_id' => $sale->id,
                    'created_by' => auth()->id(),
                ]);
            }

            $sale->delete();

            DB::commit();

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Transaksi berhasil dihapus dan stok dikembalikan.');
            $this->dispatch('sales-changed');

            $this->saleId = null;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.sales.modals.delete');
    }
}
