<?php

namespace App\Livewire\Admin\MaterialWastes\Modals;

use App\Models\MaterialWastes;
use Livewire\Component;

class Delete extends Component
{
    public $wasteId;
    public $materialName = '-';
    public $qty = 0;
    public $unitName = '';
    public $reason = '-';

    protected $listeners = ['confirm-delete' => 'loadDelete'];

    public function loadDelete($id): void
    {
        $waste = MaterialWastes::with(['material.unit'])->findOrFail($id);

        $this->wasteId = $waste->id;
        $this->materialName = $waste->material->name ?? '-';
        $this->qty = $waste->qty;
        $this->unitName = $waste->material->unit->name ?? '';
        $this->reason = $waste->reason;
    }

    public function delete(): void
    {
        $waste = MaterialWastes::findOrFail($this->wasteId);
        $waste->delete();

        $this->dispatch('close-delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data limbah berhasil dihapus.');
        $this->dispatch('material-waste-deleted');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['wasteId', 'qty']);
        $this->materialName = '-';
        $this->unitName = '';
        $this->reason = '-';
    }

    public function render()
    {
        return view('livewire.admin.material-wastes.modals.delete');
    }
}
