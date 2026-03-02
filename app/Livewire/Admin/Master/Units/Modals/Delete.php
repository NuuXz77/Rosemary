<?php

namespace App\Livewire\Admin\Master\Units\Modals;

use App\Models\Unit;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public int $unitId = 0;
    public string $name = '';

    #[On('open-delete-unit')]
    public function loadDelete(int $id): void
    {
        $unit = Unit::findOrFail($id);
        $this->unitId = $unit->id;
        $this->name   = $unit->name;
        $this->dispatch('open-modal', id: 'delete-unit-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()->can('master.units.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus satuan.');
            return;
        }

        $unit = Unit::findOrFail($this->unitId);

        if ($unit->materials()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Satuan tidak dapat dihapus karena masih digunakan oleh material.');
            $this->dispatch('close-create-modal');
            return;
        }

        try {
            $unit->delete();
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Satuan berhasil dihapus.');
            $this->dispatch('unit-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus satuan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.units.modals.delete');
    }
}
