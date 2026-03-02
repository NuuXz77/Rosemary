<?php

namespace App\Livewire\Admin\Master\Shifts\Modals;

use App\Models\Shift;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public int $shiftId = 0;
    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';
    public bool $status = true;

    #[On('open-edit-shift')]
    public function loadEdit(int $id): void
    {
        $shift = Shift::findOrFail($id);
        $this->shiftId    = $shift->id;
        $this->name       = $shift->name;
        $this->start_time = $shift->start_time;
        $this->end_time   = $shift->end_time;
        $this->status     = (bool) $shift->status;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-shift-modal');
    }

    public function update(): void
    {
        if (!auth()->user()->can('master.shifts.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah shift.');
            return;
        }

        $this->validate([
            'name'       => 'required|string|max:255',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
            'status'     => 'required|boolean',
        ], [
            'end_time.after' => 'Jam selesai harus lebih dari jam mulai.',
        ]);

        try {
            $shift = Shift::findOrFail($this->shiftId);
            $shift->update([
                'name'       => $this->name,
                'start_time' => $this->start_time,
                'end_time'   => $this->end_time,
                'status'     => $this->status,
            ]);
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Shift berhasil diperbarui.');
            $this->dispatch('shift-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui shift: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.master.shifts.modals.edit');
    }
}
