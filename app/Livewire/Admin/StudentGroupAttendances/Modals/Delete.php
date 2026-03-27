<?php

namespace App\Livewire\Admin\StudentGroupAttendances\Modals;

use App\Models\StudentGroupAttendance;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $attendanceId = null;
    public string $groupName = '-';
    public string $attendanceDate = '-';
    public string $attendanceStatus = '-';

    #[On('open-delete-group-attendance')]
    public function loadDelete(int $id): void
    {
        $attendance = StudentGroupAttendance::with('studentGroup')->findOrFail($id);

        $this->attendanceId = $attendance->id;
        $this->groupName = $attendance->studentGroup->name ?? '-';
        $this->attendanceDate = optional($attendance->date)?->translatedFormat('d F Y') ?? '-';
        $this->attendanceStatus = match ($attendance->status) {
            'on_time' => 'Tepat Waktu',
            'late' => 'Terlambat',
            default => 'Tidak Hadir',
        };
    }

    public function delete(): void
    {
        if (!auth()->user()->can('student-group-attendances.delete') && !auth()->user()->can('student-group-attendances.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus data kehadiran grup.');
            return;
        }

        $attendance = StudentGroupAttendance::findOrFail($this->attendanceId);
        $attendance->delete();

        $this->dispatch('close-delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data kehadiran grup berhasil dihapus.');
        $this->dispatch('group-attendance-deleted');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['attendanceId']);
        $this->groupName = '-';
        $this->attendanceDate = '-';
        $this->attendanceStatus = '-';
    }

    public function render()
    {
        return view('livewire.admin.student-group-attendances.modals.delete');
    }
}
