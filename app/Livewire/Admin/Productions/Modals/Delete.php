<?php

namespace App\Livewire\Admin\Productions\Modals;

use App\Models\Productions;
use Livewire\Component;

class Delete extends Component
{
    public $productionId;
    public $productName = '-';
    public $productionDate = '-';
    public $qtyProduced = 0;

    protected $listeners = ['confirm-delete' => 'loadDelete'];

    public function loadDelete($id): void
    {
        $production = Productions::with('product')->findOrFail($id);

        $this->productionId = $production->id;
        $this->productName = $production->product->name ?? '-';
        $this->productionDate = $production->production_date?->translatedFormat('d F Y') ?? '-';
        $this->qtyProduced = $production->qty_produced;
    }

    public function delete(): void
    {
        $production = Productions::findOrFail($this->productionId);

        if ($production->status === 'completed') {
            $this->dispatch('show-toast', type: 'error', message: 'Data produksi yang sudah selesai tidak bisa dihapus.');
            $this->dispatch('close-delete-modal');
            return;
        }

        $production->delete();

        $this->dispatch('close-delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Rencana produksi dihapus.');
        $this->dispatch('production-deleted');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['productionId', 'qtyProduced']);
        $this->productName = '-';
        $this->productionDate = '-';
    }

    public function render()
    {
        return view('livewire.admin.productions.modals.delete');
    }
}
