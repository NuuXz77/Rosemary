<?php

namespace App\Livewire\Admin\Schedules\Modals;

use App\Models\Schedules;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public int $scheduleId    = 0;
    public string $date       = '';
    public string $groupName  = '';

    #[On('open-delete-schedule')]
    public function loadDelete(int $id): void
    {
        $schedule = Schedules::with(['studentGroup', 'shift'])->findOrFail($id);
        $this->scheduleId = $schedule->id;
        $this->date       = $schedule->date?->translatedFormat('d M Y') ?? '-';
        $this->groupName  = $schedule->studentGroup?->name ?? '-';
        $this->dispatch('open-modal', id: 'delete-schedule-modal');
    }

    public function delete(): void
    {
        if (!auth()->user()->can('schedules.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus jadwal.');
            return;
        }

        try {
            Schedules::findOrFail($this->scheduleId)->delete();
            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Jadwal berhasil dihapus.');
            $this->dispatch('schedule-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.schedules.modals.delete');
    }
}
