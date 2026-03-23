<?php

namespace App\Livewire\Admin\StudentAttendances\Modals;

use App\Models\Schedules;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?int $attendanceId = null;
    public ?int $schedule_id = null;
    public ?string $login_time = null;
    public string $status = 'on_time';

    protected function rules(): array
    {
        return [
            'schedule_id' => 'required|exists:schedules,id|unique:student_attendances,schedule_id,' . $this->attendanceId,
            'status' => 'required|in:on_time,late,absent',
            'login_time' => 'nullable|required_unless:status,absent|date_format:H:i',
        ];
    }

    #[On('open-edit-attendance')]
    public function loadEdit(int $id): void
    {
        $attendance = StudentAttendance::findOrFail($id);

        $this->attendanceId = $attendance->id;
        $this->schedule_id = $attendance->schedule_id;
        $this->status = $attendance->status;
        $this->login_time = optional($attendance->login_time)?->format('H:i');

        $this->resetValidation();
    }

    public function update(): void
    {
        if (!auth()->user()->can('schedules.edit') && !auth()->user()->can('schedules.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah data kehadiran.');
            return;
        }

        $validated = $this->validate();

        $schedule = Schedules::with('shift')
            ->where('type', 'cashier')
            ->findOrFail($validated['schedule_id']);

        if (!$schedule->student_id || !$schedule->shift_id) {
            $this->dispatch('show-toast', type: 'error', message: 'Jadwal tidak valid untuk data kehadiran siswa.');
            return;
        }

        $shiftStart = optional($schedule->shift?->start_time)->format('H:i:s') ?? '00:00:00';
        $lateMinutes = $this->calculateLateMinutes($validated['status'], $validated['login_time'] ?? null, $shiftStart);

        $attendance = StudentAttendance::findOrFail($this->attendanceId);
        $attendance->update([
            'student_id' => $schedule->student_id,
            'schedule_id' => $schedule->id,
            'shift_id' => $schedule->shift_id,
            'date' => $schedule->date,
            'login_time' => $validated['status'] === 'absent' ? null : ($validated['login_time'] ? $validated['login_time'] . ':00' : null),
            'shift_start' => $shiftStart,
            'status' => $validated['status'],
            'late_minutes' => $lateMinutes,
        ]);

        $this->dispatch('close-edit-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data kehadiran siswa berhasil diperbarui.');
        $this->dispatch('attendance-updated');

        $this->resetForm();
    }

    private function calculateLateMinutes(string $status, ?string $loginTime, string $shiftStart): int
    {
        if ($status !== 'late' || !$loginTime) {
            return 0;
        }

        try {
            $shiftStartTime = Carbon::createFromTimeString($shiftStart);
            $loginTimeValue = Carbon::createFromTimeString($loginTime . ':00');

            return (int) max(0, $shiftStartTime->diffInMinutes($loginTimeValue, false));
        } catch (\Throwable) {
            return 0;
        }
    }

    public function updatedStatus(string $value): void
    {
        if ($value === 'absent') {
            $this->login_time = null;
        }
    }

    public function resetForm(): void
    {
        $this->reset(['attendanceId', 'schedule_id', 'login_time', 'status']);
        $this->status = 'on_time';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.student-attendances.modals.edit', [
            'schedules' => Schedules::with(['student.schoolClass', 'shift'])
                ->where('type', 'cashier')
                ->whereNotNull('student_id')
                ->where('status', true)
                ->orderByDesc('date')
                ->limit(200)
                ->get(),
        ]);
    }
}
