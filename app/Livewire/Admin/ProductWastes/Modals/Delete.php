<?php

namespace App\Livewire\Admin\ProductWastes\Modals;

use App\Models\ProductWastes;
use Livewire\Component;

class Delete extends Component
{
    public $wasteId;
    public $productName = '-';
    public $qty = 0;
    public $reason = '-';

    protected $listeners = ['confirm-delete' => 'loadDelete'];

    public function loadDelete($id): void
    {
        $waste = ProductWastes::with('product')->findOrFail($id);

        $this->wasteId = $waste->id;
        $this->productName = $waste->product->name ?? '-';
        $this->qty = $waste->qty;
        $this->reason = $waste->reason;
    }

    public function delete(): void
    {
        $waste = ProductWastes::findOrFail($this->wasteId);
        $waste->delete();

        $this->dispatch('close-delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data limbah produk berhasil dihapus.');
        $this->dispatch('product-waste-deleted');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['wasteId', 'qty']);
        $this->productName = '-';
        $this->reason = '-';
    }

    public function render()
    {
        return view('livewire.admin.product-wastes.modals.delete');
    }
}
