<?php

namespace App\Livewire\Admin\Schedules\Modals;

use App\Models\Classes;
use App\Models\Divisions;
use App\Models\Schedules;
use App\Models\Shift;
use App\Models\Students;
use App\Models\StudentGroups;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class AutoGenerate extends Component
{
    public int $month;
    public int $year;
    public string $start_date          = '';
    public string $end_date            = '';
    public string $type                 = 'production';
    public int|string $shift_id         = '';
    // Cashier
    public int|string $class_id         = '';
    // Production
    public int|string $division_id      = '';
    public int|string $student_group_id = '';
    public bool $skipWeekends           = true;
    public bool $overwriteExisting      = false;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year  = now()->year;
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->endOfMonth()->toDateString();
    }

    #[On('open-autogenerate-schedule')]
    public function openModal(int $month, int $year): void
    {
        $this->month            = $month;
        $this->year             = $year;
        $this->start_date       = Carbon::create($year, $month, 1)->toDateString();
        $this->end_date         = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        $this->type             = 'production';
        $this->shift_id         = '';
        $this->class_id         = '';
        $this->division_id      = '';
        $this->student_group_id = '';
        $this->skipWeekends     = true;
        $this->overwriteExisting = false;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'autogenerate-schedule-modal');
    }

    public function generate(): void
    {
        if (!auth()->user()->can('schedules.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk membuat jadwal otomatis.');
            return;
        }

        $base = [
            'type'     => 'required|in:cashier,production',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'shift_id' => 'required|exists:shifts,id',
        ];

        if ($this->type === 'cashier') {
            $this->validate(array_merge($base, [
                'class_id' => 'required|exists:classes,id',
            ]));
        } else {
            $this->validate(array_merge($base, [
                'division_id'      => 'required|exists:divisions,id',
                'student_group_id' => 'required|exists:student_groups,id',
            ]));
        }

        try {
            $startDate = Carbon::parse($this->start_date)->startOfDay();
            $endDate   = Carbon::parse($this->end_date)->startOfDay();
            $created   = 0;
            $skipped   = 0;

            // ── Cashier rolling setup ──────────────────────────────────
            $roster       = collect();
            $rosterIds    = collect();
            $rosterPos    = 0;
            $rosterCount  = 0;

            if ($this->type === 'cashier') {
                $roster = Students::where('status', true)
                    ->where('class_id', $this->class_id)
                    ->orderBy('name')
                    ->get();

                if ($roster->isEmpty()) {
                    $this->dispatch('show-toast', type: 'error', message: 'Tidak ada siswa aktif di kelas ini.');
                    return;
                }

                $rosterIds   = $roster->pluck('id');
                $rosterCount = $roster->count();

                // Find the last scheduled student for this class+shift before the target month
                // to continue the rotation from where it left off
                $last = Schedules::where('type', 'cashier')
                    ->where('shift_id', $this->shift_id)
                    ->whereIn('student_id', $rosterIds)
                    ->where('date', '<', $startDate->toDateString())
                    ->orderBy('date', 'desc')
                    ->first();

                if ($last) {
                    $lastIdx   = $roster->search(fn($s) => $s->id == $last->student_id);
                    $rosterPos = ($lastIdx === false ? 0 : $lastIdx + 1) % $rosterCount;
                }
            }
            // ──────────────────────────────────────────────────────────

            $current = $startDate->copy();
            while ($current <= $endDate) {
                if ($this->skipWeekends && $current->isWeekend()) {
                    $current->addDay();
                    continue;
                }

                $dateStr = $current->toDateString();

                if ($this->type === 'cashier') {
                    $existing = Schedules::where('date', $dateStr)
                        ->where('type', 'cashier')
                        ->where('shift_id', $this->shift_id)
                        ->whereIn('student_id', $rosterIds)
                        ->first();

                    if ($existing && !$this->overwriteExisting) {
                        $skipped++;
                        $current->addDay();
                        continue;
                    }

                    $student = $roster[$rosterPos % $rosterCount];

                    if ($existing) {
                        $existing->update(['student_id' => $student->id, 'shift_id' => $this->shift_id, 'status' => true]);
                    } else {
                        Schedules::create([
                            'type'       => 'cashier',
                            'date'       => $dateStr,
                            'shift_id'   => $this->shift_id,
                            'student_id' => $student->id,
                            'status'     => true,
                        ]);
                        $created++;
                    }

                    $rosterPos++;
                } else {
                    $exists = Schedules::where('date', $dateStr)
                        ->where('type', 'production')
                        ->where('student_group_id', $this->student_group_id)
                        ->where('division_id', $this->division_id)
                        ->exists();

                    if ($exists && !$this->overwriteExisting) {
                        $skipped++;
                        $current->addDay();
                        continue;
                    }

                    if ($exists && $this->overwriteExisting) {
                        Schedules::where('date', $dateStr)
                            ->where('type', 'production')
                            ->where('student_group_id', $this->student_group_id)
                            ->where('division_id', $this->division_id)
                            ->update(['shift_id' => $this->shift_id, 'status' => true]);
                    } else {
                        Schedules::create([
                            'type'             => 'production',
                            'date'             => $dateStr,
                            'shift_id'         => $this->shift_id,
                            'student_group_id' => $this->student_group_id,
                            'division_id'      => $this->division_id,
                            'status'           => true,
                        ]);
                        $created++;
                    }
                }

                $current->addDay();
            }

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: "Berhasil membuat {$created} jadwal." . ($skipped > 0 ? " {$skipped} jadwal dilewati (sudah ada)." : ''));
            $this->dispatch('schedule-changed');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal membuat jadwal otomatis: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.schedules.modals.auto-generate', [
            'shifts'        => Shift::where('status', true)->orderBy('name')->get(),
            'classes'       => Classes::where('status', true)->orderBy('name')->get(),
            'studentGroups' => StudentGroups::where('status', true)->orderBy('name')->get(),
            'divisions'     => Divisions::where('status', true)->where('type', 'production')->orderBy('name')->get(),
        ]);
    }
}
