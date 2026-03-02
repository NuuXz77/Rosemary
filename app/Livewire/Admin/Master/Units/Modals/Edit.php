<?php

namespace App\Livewire\Admin\Master\Units\Modals;

use App\Models\Unit;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public int $unitId = 0;
    public string $name = '';
    public bool $status = true;

    #[On('open-edit-unit')]
    public function loadEdit(int $id): void
    {
        $unit = Unit::findOrFail($id);
        $this->unitId = $unit->id;
        $this->name   = $unit->name;
        $this->status = (bool) $unit->status;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-unit-modal');
    }

    public function update(): void
    {
        if (!auth()->user()->can('master.units.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah satuan.');
            return;
        }

        $this->validate([
            'name'   => 'required|string|max:255|unique:units,name,' . $this->unitId,
            'status' => 'required|boolean',
        ]);

        try {
            $unit = Unit::findOrFail($this->unitId);
            $unit->update(['name' => $this->name, 'status' => $this->status]);
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Satuan berhasil diperbarui.');
            $this->dispatch('unit-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui satuan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.units.modals.edit');
    }
}
