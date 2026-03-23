<?php

namespace App\Livewire\Admin\Purchases\Modals;

use App\Models\Purchases;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $purchaseId = null;

    #[On('open-delete-purchase')]
    public function loadDelete(int $id): void
    {
        $this->purchaseId = $id;
        $this->dispatch('open-modal', id: 'delete-purchase-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()->can('purchases.delete') && !auth()->user()->can('purchases.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus pembelian.');
            return;
        }

        try {
            $purchase = Purchases::findOrFail($this->purchaseId);
            $purchase->delete();

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Purchase berhasil dihapus.');
            $this->dispatch('purchase-changed');
            $this->purchaseId = null;
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus purchase: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.purchases.modals.delete');
    }
}
