<?php

namespace App\Livewire\Admin\Master\Divisions\Modals;

use App\Models\Divisions;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public int $divisionId = 0;
    public string $name = '';

    #[On('open-delete-division')]
    public function loadDelete(int $id): void
    {
        $division = Divisions::findOrFail($id);
        $this->divisionId = $division->id;
        $this->name       = $division->name;
        $this->dispatch('open-modal', id: 'delete-division-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()->can('master.divisions.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus divisi.');
            return;
        }

        $division = Divisions::findOrFail($this->divisionId);

        if ($division->products()->count() > 0 || $division->schedules()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Divisi tidak dapat dihapus karena masih digunakan.');
            $this->dispatch('close-create-modal');
            return;
        }

        try {
            $division->delete();
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Divisi berhasil dihapus.');
            $this->dispatch('division-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus divisi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.divisions.modals.delete');
    }
}
