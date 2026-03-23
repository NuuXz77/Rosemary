<?php

namespace App\Livewire\Admin\StudentAttendances\Modals;

use App\Models\StudentAttendance;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public ?int $attendanceId = null;
    public string $studentName = '-';
    public string $attendanceDate = '-';
    public string $attendanceStatus = '-';

    #[On('open-delete-attendance')]
    public function loadDelete(int $id): void
    {
        $attendance = StudentAttendance::with('student')->findOrFail($id);

        $this->attendanceId = $attendance->id;
        $this->studentName = $attendance->student->name ?? '-';
        $this->attendanceDate = optional($attendance->date)?->translatedFormat('d F Y') ?? '-';
        $this->attendanceStatus = match ($attendance->status) {
            'on_time' => 'Tepat Waktu',
            'late' => 'Terlambat',
            default => 'Tidak Hadir',
        };
    }

    public function delete(): void
    {
        if (!auth()->user()->can('schedules.delete') && !auth()->user()->can('schedules.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus data kehadiran.');
            return;
        }

        $attendance = StudentAttendance::findOrFail($this->attendanceId);
        $attendance->delete();

        $this->dispatch('close-delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data kehadiran siswa berhasil dihapus.');
        $this->dispatch('attendance-deleted');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['attendanceId']);
        $this->studentName = '-';
        $this->attendanceDate = '-';
        $this->attendanceStatus = '-';
    }

    public function render()
    {
        return view('livewire.admin.student-attendances.modals.delete');
    }
}
