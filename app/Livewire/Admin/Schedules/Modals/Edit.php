<?php

namespace App\Livewire\Admin\Schedules\Modals;

use App\Models\Classes;
use App\Models\Divisions;
use App\Models\Schedules;
use App\Models\Shift;
use App\Models\Students;
use App\Models\StudentGroups;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public int $scheduleId         = 0;
    public string $type            = 'production';
    public string $date            = '';
    public int|string $shift_id    = '';
    // Cashier
    public int|string $class_id    = ''; // UI filter only
    public int|string $student_id  = '';
    // Production
    public int|string $division_id = '';
    public int|string $student_group_id = '';
    public bool $status            = true;

    #[On('open-edit-schedule')]
    public function loadEdit(int $id): void
    {
        $schedule = Schedules::findOrFail($id);
        $this->scheduleId       = $schedule->id;
        $this->type             = $schedule->type ?? 'production';
        $this->date             = $schedule->date?->format('Y-m-d') ?? '';
        $this->shift_id         = $schedule->shift_id ?? '';
        $this->student_id       = $schedule->student_id ?? '';
        $this->class_id         = $schedule->student?->class_id ?? '';
        $this->student_group_id = $schedule->student_group_id ?? '';
        $this->division_id      = $schedule->division_id ?? '';
        $this->status           = (bool) $schedule->status;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'edit-schedule-modal');
    }

    public function update(): void
    {
        if (!auth()->user()->can('schedules.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah jadwal.');
            return;
        }

        $base = [
            'type'     => 'required|in:cashier,production',
            'date'     => 'required|date',
            'shift_id' => 'required|exists:shifts,id',
            'status'   => 'required|boolean',
        ];

        if ($this->type === 'cashier') {
            $this->validate(array_merge($base, [
                'student_id' => 'required|exists:students,id',
            ]));
        } else {
            $this->validate(array_merge($base, [
                'division_id'      => 'required|exists:divisions,id',
                'student_group_id' => 'required|exists:student_groups,id',
            ]));
        }

        try {
            $schedule = Schedules::findOrFail($this->scheduleId);

            if ($this->type === 'cashier') {
                $duplicate = Schedules::where('date', $this->date)
                    ->where('type', 'cashier')
                    ->where('student_id', $this->student_id)
                    ->where('shift_id', $this->shift_id)
                    ->where('id', '!=', $this->scheduleId)
                    ->exists();
                if ($duplicate) {
                    $this->addError('student_id', 'Siswa ini sudah memiliki jadwal kasir pada tanggal dan shift tersebut.');
                    return;
                }
                $schedule->update([
                    'type'             => 'cashier',
                    'date'             => $this->date,
                    'shift_id'         => $this->shift_id,
                    'student_id'       => $this->student_id,
                    'student_group_id' => null,
                    'division_id'      => null,
                    'status'           => $this->status,
                ]);
            } else {
                $duplicate = Schedules::where('date', $this->date)
                    ->where('type', 'production')
                    ->where('student_group_id', $this->student_group_id)
                    ->where('division_id', $this->division_id)
                    ->where('id', '!=', $this->scheduleId)
                    ->exists();
                if ($duplicate) {
                    $this->addError('student_group_id', 'Kelompok ini sudah dijadwalkan pada divisi yang sama di tanggal tersebut.');
                    return;
                }
                $schedule->update([
                    'type'             => 'production',
                    'date'             => $this->date,
                    'shift_id'         => $this->shift_id,
                    'student_id'       => null,
                    'student_group_id' => $this->student_group_id,
                    'division_id'      => $this->division_id,
                    'status'           => $this->status,
                ]);
            }

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Jadwal berhasil diperbarui.');
            $this->dispatch('schedule-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.schedules.modals.edit', [
            'shifts'        => Shift::where('status', true)->orderBy('name')->get(),
            'classes'       => Classes::where('status', true)->orderBy('name')->get(),
            'students'      => Students::where('status', true)
                                ->when($this->class_id, fn($q) => $q->where('class_id', $this->class_id))
                                ->orderBy('name')->get(),
            'studentGroups' => StudentGroups::where('status', true)->orderBy('name')->get(),
            'divisions'     => Divisions::where('status', true)->where('type', 'production')->orderBy('name')->get(),
        ]);
    }
}
