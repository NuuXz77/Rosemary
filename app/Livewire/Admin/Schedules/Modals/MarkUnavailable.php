<?php

namespace App\Livewire\Admin\Schedules\Modals;

use App\Models\Schedules;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class MarkUnavailable extends Component
{
    public int $scheduleId = 0;
    public string $absence_type = Schedules::ABSENCE_SICK;
    public string $absence_note = '';
    public int|string $replacement_schedule_id = '';
    public array $replacementCandidates = [];
    public ?Schedules $schedule = null;

    #[On('open-mark-unavailable-schedule')]
    public function openModal(int $id): void
    {
        $this->schedule = Schedules::with(['student', 'shift'])->findOrFail($id);
        $currentAbsenceType = $this->normalizeAbsenceType($this->schedule->absence_type);

        if ($this->schedule->type !== 'cashier' || !$this->schedule->student_id) {
            $this->dispatch('show-toast', type: 'error', message: 'Fitur berhalangan otomatis saat ini khusus jadwal kasir.');
            return;
        }

        if ($currentAbsenceType !== Schedules::ABSENCE_NONE) {
            $this->dispatch(
                'show-toast',
                type: 'warning',
                message: 'Jadwal ini sudah diproses sebelumnya (' . $this->absenceLabel($currentAbsenceType) . ').'
            );
            return;
        }

        $candidates = $this->getReplacementCandidates($this->schedule);
        $this->replacementCandidates = $candidates
            ->map(fn (Schedules $candidate) => [
                'id' => $candidate->id,
                'student_name' => $candidate->student?->name ?? '-',
                'date' => $candidate->date?->format('d/m/Y') ?? '-',
                'shift_name' => $candidate->shift?->name ?? '-',
            ])
            ->values()
            ->all();

        $this->scheduleId = $this->schedule->id;
        $this->absence_type = Schedules::ABSENCE_SICK;
        $this->absence_note = '';
        $this->replacement_schedule_id = $this->replacementCandidates[0]['id'] ?? '';
        $this->resetValidation();

        $this->dispatch('open-modal', id: 'mark-unavailable-schedule-modal');
    }

    public function submit(): void
    {
        if (!auth()->user()->can('schedules.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah jadwal.');
            return;
        }

        $this->validate([
            'absence_type' => 'required|in:sick,permit,leave,other',
            'absence_note' => 'nullable|string|max:255',
            'replacement_schedule_id' => 'required|integer',
        ]);

        try {
            DB::transaction(function () {
                $schedule = Schedules::query()
                    ->lockForUpdate()
                    ->with(['student', 'shift'])
                    ->findOrFail($this->scheduleId);

                if ($schedule->type !== 'cashier' || !$schedule->student_id) {
                    throw new \RuntimeException('Jadwal ini bukan jadwal kasir.');
                }

                $currentAbsenceType = $this->normalizeAbsenceType($schedule->absence_type);
                if ($currentAbsenceType !== Schedules::ABSENCE_NONE) {
                    throw new \RuntimeException('Jadwal ini sudah diproses sebelumnya (' . $this->absenceLabel($currentAbsenceType) . ').');
                }

                $candidate = $this->findReplacementCandidateById($schedule, (int) $this->replacement_schedule_id);
                if (!$candidate) {
                    throw new \RuntimeException('Siswa pengganti tidak tersedia atau sudah dipakai. Silakan pilih kandidat lain.');
                }

                $schedule->update([
                    'absence_type' => $this->absence_type,
                    'absence_note' => $this->absence_note ?: 'Berhalangan, menunggu/ditangani auto pengganti.',
                ]);

                $replacementSchedule = Schedules::create([
                    'type' => 'cashier',
                    'date' => $schedule->date,
                    'shift_id' => $schedule->shift_id,
                    'student_id' => $candidate->student_id,
                    'student_group_id' => null,
                    'division_id' => null,
                    'status' => true,
                    'absence_type' => Schedules::ABSENCE_NONE,
                    'absence_note' => 'Pengganti manual untuk ' . ($schedule->student?->name ?? 'siswa') . ' (' . $this->absence_type . ')',
                    'replaced_from_schedule_id' => $candidate->id,
                    'replaced_by_schedule_id' => null,
                ]);

                $candidate->update([
                    'status' => false,
                    'absence_type' => Schedules::ABSENCE_RESCHEDULED,
                    'absence_note' => 'Dipindah ke ' . $schedule->date?->format('d/m/Y') . ' menggantikan ' . ($schedule->student?->name ?? 'siswa'),
                    'replaced_by_schedule_id' => $replacementSchedule->id,
                ]);

                $schedule->update([
                    'replaced_by_schedule_id' => $replacementSchedule->id,
                ]);

                $alreadyHasMakeup = Schedules::query()
                    ->where('type', 'cashier')
                    ->whereDate('date', $candidate->date)
                    ->where('shift_id', $candidate->shift_id)
                    ->where('student_id', $schedule->student_id)
                    ->exists();

                if (!$alreadyHasMakeup) {
                    Schedules::create([
                        'type' => 'cashier',
                        'date' => $candidate->date,
                        'shift_id' => $candidate->shift_id,
                        'student_id' => $schedule->student_id,
                        'student_group_id' => null,
                        'division_id' => null,
                        'status' => true,
                        'absence_type' => Schedules::ABSENCE_NONE,
                        'absence_note' => 'Jadwal pengganti untuk ketidakhadiran ' . $schedule->date?->format('d/m/Y'),
                        'replaced_from_schedule_id' => $schedule->id,
                        'replaced_by_schedule_id' => null,
                    ]);
                }
            });

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Jadwal berhalangan diproses. Pengganti berhasil dijadwalkan.');
            $this->dispatch('schedule-changed');
        } catch (\Throwable $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memproses berhalangan: ' . $e->getMessage());
        }
    }

    private function getReplacementCandidates(Schedules $schedule)
    {
        $fromDate = Carbon::parse($schedule->date)->addDay();
        $nextMonday = Carbon::parse($schedule->date)->next(Carbon::MONDAY)->toDateString();

        return Schedules::query()
            ->where('type', 'cashier')
            ->where('status', true)
            ->where(function ($query) {
                $query->where('absence_type', Schedules::ABSENCE_NONE)
                    ->orWhereNull('absence_type');
            })
            ->whereDate('date', '>=', $fromDate)
            ->where('shift_id', $schedule->shift_id)
            ->whereNotNull('student_id')
            ->where('student_id', '!=', $schedule->student_id)
            ->whereNull('replaced_by_schedule_id')
            ->with(['student', 'shift'])
            ->orderBy('date')
            ->get()
            ->filter(function (Schedules $candidate) use ($schedule) {
                return !Schedules::query()
                ->where('type', 'cashier')
                ->whereDate('date', $schedule->date)
                ->where('shift_id', $schedule->shift_id)
                ->where('student_id', $candidate->student_id)
                ->exists();
            })
            ->sortBy(fn(Schedules $candidate) => [
                $candidate->date?->toDateString() === $nextMonday ? 0 : 1,
                $candidate->date?->toDateString(),
            ])
            ->values();
    }

    private function findReplacementCandidateById(Schedules $schedule, int $candidateScheduleId): ?Schedules
    {
        return $this->getReplacementCandidates($schedule)
            ->first(fn (Schedules $candidate) => $candidate->id === $candidateScheduleId);
    }

    private function normalizeAbsenceType(?string $type): string
    {
        return $type ?: Schedules::ABSENCE_NONE;
    }

    private function absenceLabel(?string $type): string
    {
        $type = $this->normalizeAbsenceType($type);

        return match ($type) {
            Schedules::ABSENCE_NONE => 'Belum diproses',
            Schedules::ABSENCE_SICK => 'Sakit',
            Schedules::ABSENCE_PERMIT => 'Izin',
            Schedules::ABSENCE_LEAVE => 'Cuti',
            Schedules::ABSENCE_OTHER => 'Lainnya',
            Schedules::ABSENCE_RESCHEDULED => 'Dipindah',
            default => 'Diproses',
        };
    }

    public function render()
    {
        return view('livewire.admin.schedules.modals.mark-unavailable');
    }
}
