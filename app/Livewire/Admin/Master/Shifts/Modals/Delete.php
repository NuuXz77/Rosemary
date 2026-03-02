<?php

namespace App\Livewire\Admin\Master\Shifts\Modals;

use App\Models\Shift;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public int $shiftId = 0;
    public string $name = '';

    #[On('open-delete-shift')]
    public function loadDelete(int $id): void
    {
        $shift = Shift::findOrFail($id);
        $this->shiftId = $shift->id;
        $this->name    = $shift->name;
        $this->dispatch('open-modal', id: 'delete-shift-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()->can('master.shifts.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus shift.');
            return;
        }

        $shift = Shift::findOrFail($this->shiftId);

        if ($shift->productions()->count() > 0 || $shift->sales()->count() > 0 || $shift->schedules()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Shift tidak dapat dihapus karena masih digunakan.');
            $this->dispatch('close-create-modal');
            return;
        }

        try {
            $shift->delete();
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Shift berhasil dihapus.');
            $this->dispatch('shift-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus shift: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.shifts.modals.delete');
    }
}
