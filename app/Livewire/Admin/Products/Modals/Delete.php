<?php

namespace App\Livewire\Admin\Products\Modals;

use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $productId = null;

    #[On('open-delete-product')]
    public function loadDelete(int $id): void
    {
        $this->productId = $id;
        $this->dispatch('open-modal', id: 'delete-product-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()->can('products.delete')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus produk.');
            return;
        }

        try {
            $product = Products::findOrFail($this->productId);

            if ($product->saleItems()->count() > 0 || $product->productions()->count() > 0 || $product->stockLogs()->count() > 0 || $product->productWastes()->count() > 0) {
                $this->dispatch('show-toast', type: 'error', message: 'Produk tidak bisa dihapus karena sudah memiliki riwayat transaksi, produksi, atau limbah.');
                $this->dispatch('close-create-modal');
                return;
            }

            DB::beginTransaction();

            if ($product->foto_product && Storage::disk('public')->exists($product->foto_product)) {
                Storage::disk('public')->delete($product->foto_product);
            }

            $product->stock()->delete();
            $product->delete();

            DB::commit();

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Produk berhasil dihapus.');
            $this->dispatch('product-changed');
            $this->productId = null;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.products.modals.delete');
    }
}
