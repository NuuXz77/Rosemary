<?php

namespace App\Livewire\Admin\Master\Divisions\Modals;

use App\Models\Divisions;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public int $divisionId = 0;
    public string $name = '';
    public string $type = 'production';
    public bool $status = true;

    #[On('open-edit-division')]
    public function loadEdit(int $id): void
    {
        $division = Divisions::findOrFail($id);
        $this->divisionId = $division->id;
        $this->name       = $division->name;
        $this->type       = $division->type;
        $this->status     = (bool) $division->status;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-division-modal');
    }

    public function update(): void
    {
        if (!auth()->user()->can('master.divisions.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah divisi.');
            return;
        }

        $this->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'required|in:cashier,production',
            'status' => 'required|boolean',
        ]);

        try {
            $division = Divisions::findOrFail($this->divisionId);
            $division->update(['name' => $this->name, 'type' => $this->type, 'status' => $this->status]);
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Divisi berhasil diperbarui.');
            $this->dispatch('division-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui divisi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.divisions.modals.edit');
    }
}
