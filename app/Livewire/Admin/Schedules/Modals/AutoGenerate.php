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
    public string $start_date               = '';
    public string $end_date                 = '';
    public string $type                     = 'production';
    public int|string $shift_id             = '';
    // Production
    public int|string $division_id          = '';
    public array $class_ids                 = []; // MULTIPLE kelas untuk production (esp Café & Resto)
    public bool $autoGenerateCashier        = false; // Auto-generate cashier dari kelompok production Café & Resto
    // Cashier (manual mode)
    public int|string $cashier_class_id     = '';
    public bool $skipWeekends               = true;
    public bool $overwriteExisting          = false;

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
        $this->month                = $month;
        $this->year                 = $year;
        $this->start_date           = Carbon::create($year, $month, 1)->toDateString();
        $this->end_date             = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        $this->type                 = 'production';
        $this->shift_id             = '';
        $this->division_id          = '';
        $this->class_ids            = [];
        $this->autoGenerateCashier  = false;
        $this->cashier_class_id     = '';
        $this->skipWeekends         = true;
        $this->overwriteExisting    = false;
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
            'type'        => 'required|in:cashier,production',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'shift_id'    => 'required|exists:shifts,id',
        ];

        try {
            if ($this->type === 'cashier') {
                $this->validate(array_merge($base, [
                    'cashier_class_id' => 'required|exists:classes,id',
                ]));
            } else {
                $this->validate(array_merge($base, [
                    'division_id' => 'required|exists:divisions,id',
                    'class_ids'   => 'required|array|min:1',
                    'class_ids.*' => 'exists:classes,id',
                ]));
            }

            $startDate = Carbon::parse($this->start_date)->startOfDay();
            $endDate   = Carbon::parse($this->end_date)->startOfDay();
            $created   = 0;
            $skipped   = 0;

            if ($this->type === 'cashier') {
                $this->generateCashierSchedules($startDate, $endDate, $created, $skipped);
            } else {
                // Generate production schedule
                $this->generateProductionSchedules($startDate, $endDate, $created, $skipped);
                
                // Auto-generate cashier jika Café & Resto dan enabled
                $division = Divisions::find($this->division_id);
                if ($this->autoGenerateCashier && $division && $division->name === 'Café & Resto') {
                    $this->generateCashierFromCafeResto($startDate, $endDate, $created, $skipped);
                }
            }

            $this->dispatch('show-toast', type: 'success', message: "Berhasil membuat {$created} jadwal." . ($skipped > 0 ? " {$skipped} jadwal dilewati (sudah ada)." : ''));
            $this->dispatch('schedule-changed');
            $this->dispatch('close-create-modal');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $message = 'Validasi gagal: ';
            foreach ($e->errors() as $field => $errors) {
                $message .= implode(', ', $errors) . ' ';
            }
            $this->dispatch('show-toast', type: 'error', message: trim($message));
        } catch (\Exception $e) {
            \Log::error('Auto Generate Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            $this->dispatch('show-toast', type: 'error', message: 'Gagal membuat jadwal otomatis: ' . $e->getMessage());
        }
    }

    /**
     * CASHIER MANUAL: Rolling per day untuk satu kelas tertentu
     */
    private function generateCashierSchedules(Carbon $startDate, Carbon $endDate, &$created, &$skipped): void
    {
        $roster = Students::where('status', true)
            ->where('class_id', $this->cashier_class_id)
            ->orderBy('name')
            ->get();

        if ($roster->isEmpty()) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak ada siswa aktif di kelas ini.');
            return;
        }

        $rosterIds   = $roster->pluck('id');
        $rosterCount = $roster->count();
        $rosterPos   = 0;

        $last = Schedules::where('type', 'cashier')
            ->where('shift_id', $this->shift_id)
            ->where('class_id', $this->cashier_class_id)
            ->whereIn('student_id', $rosterIds)
            ->where('date', '<', $startDate->toDateString())
            ->orderBy('date', 'desc')
            ->first();

        if ($last) {
            $lastIdx   = $roster->search(fn($s) => $s->id == $last->student_id);
            $rosterPos = ($lastIdx === false ? 0 : $lastIdx + 1) % $rosterCount;
        }

        $current = $startDate->copy();
        while ($current <= $endDate) {
            if ($this->skipWeekends && $current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $dateStr = $current->toDateString();

            $existing = Schedules::where('date', $dateStr)
                ->where('type', 'cashier')
                ->where('shift_id', $this->shift_id)
                ->where('class_id', $this->cashier_class_id)
                ->whereIn('student_id', $rosterIds)
                ->first();

            if ($existing && !$this->overwriteExisting) {
                $skipped++;
                $current->addDay();
                continue;
            }

            $student = $roster[$rosterPos % $rosterCount];

            if ($existing) {
                $existing->update([
                    'student_id' => $student->id,
                    'shift_id'   => $this->shift_id,
                    'status'     => true,
                ]);
            } else {
                Schedules::create([
                    'type'       => 'cashier',
                    'date'       => $dateStr,
                    'shift_id'   => $this->shift_id,
                    'student_id' => $student->id,
                    'class_id'   => $this->cashier_class_id,
                    'status'     => true,
                ]);
                $created++;
            }

            $rosterPos++;
            $current->addDay();
        }
    }

    /**
     * PRODUCTION: Rolling per 3 minggu untuk multiple kelas
     * Kelompok dari kelas-kelas terpilih akan rolling setiap 3 minggu ke divisi
     */
    private function generateProductionSchedules(Carbon $startDate, Carbon $endDate, &$created, &$skipped): void
    {
        $selectedClassIds = collect($this->class_ids)
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        // Get semua kelompok dari kelas-kelas terpilih (tidak filter division karena kelompok bisa flexible)
        $allGroups = StudentGroups::where('status', true)
            ->whereIn('class_id', $selectedClassIds)
            ->orderBy('class_id')
            ->orderBy('name')
            ->get();

        if ($allGroups->isEmpty()) {
            $classNames = Classes::whereIn('id', $selectedClassIds)->pluck('name')->implode(', ');
            $this->dispatch('show-toast', type: 'error', message: "Tidak ada kelompok aktif di kelas: {$classNames}. Pastikan kelompok sudah dibuat dan status aktif.");
            return;
        }

        $groupsByClass = $allGroups->groupBy('class_id');
        $missingClassIds = collect($selectedClassIds)
            ->filter(fn($classId) => !$groupsByClass->has($classId))
            ->values();

        if ($missingClassIds->isNotEmpty()) {
            $missingClassNames = Classes::whereIn('id', $missingClassIds)->pluck('name')->implode(', ');
            $this->dispatch('show-toast', type: 'error', message: "Kelompok belum tersedia untuk kelas: {$missingClassNames}.");
            return;
        }

        $current = $startDate->copy();
        $groupPositions = [];

        foreach ($selectedClassIds as $classId) {
            $classGroups = $groupsByClass->get($classId);
            $groupPositions[$classId] = 0;

            if (!$classGroups || $classGroups->isEmpty()) {
                continue;
            }

            $lastClassProd = Schedules::where('type', 'production')
                ->where('division_id', $this->division_id)
                ->where('shift_id', $this->shift_id)
                ->where('date', '<', $startDate->toDateString())
                ->whereIn('student_group_id', $classGroups->pluck('id'))
                ->orderBy('date', 'desc')
                ->first();

            if ($lastClassProd && $lastClassProd->student_group_id) {
                $lastIdx = $classGroups->search(fn($group) => $group->id == $lastClassProd->student_group_id);
                $groupPositions[$classId] = ($lastIdx === false ? 0 : $lastIdx + 1) % $classGroups->count();
            }
        }

        $cycleStartDate = $startDate->copy();
        $nextCycleDate = $cycleStartDate->copy()->addWeeks(3);
        $currentWeekKey = null;
        $weeklySlot = 0;
        $noAvailableGroupWarned = false;

        while ($current <= $endDate) {
            if ($this->skipWeekends && $current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $weekKey = $current->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
            if ($currentWeekKey !== $weekKey) {
                $currentWeekKey = $weekKey;
                $weeklySlot = 0;
            }

            if ($current >= $nextCycleDate) {
                foreach ($selectedClassIds as $classId) {
                    $groupPositions[$classId]++;
                }
                $cycleStartDate = $current->copy();
                $nextCycleDate = $cycleStartDate->copy()->addWeeks(3);
            }

            // Jika pilih tepat 2 kelas, gunakan pola 2 hari per kelas dan Jumat dikosongkan.
            if (count($selectedClassIds) === 2 && $current->isFriday()) {
                $current->addDay();
                continue;
            }

            if (count($selectedClassIds) > 1) {
                $classIndex = intdiv($weeklySlot, 2) % count($selectedClassIds);
                $targetClassId = $selectedClassIds[$classIndex];
            } else {
                $targetClassId = $selectedClassIds[0];
            }

            $weeklySlot++;

            $classGroups = $groupsByClass->get($targetClassId);
            if (!$classGroups || $classGroups->isEmpty()) {
                $current->addDay();
                continue;
            }

            $dateStr = $current->toDateString();
            $existing = Schedules::where('date', $dateStr)
                ->where('type', 'production')
                ->where('division_id', $this->division_id)
                ->first();

            $assignedGroupIds = Schedules::where('date', $dateStr)
                ->where('type', 'production')
                ->when($existing, fn($q) => $q->where('id', '!=', $existing->id))
                ->pluck('student_group_id')
                ->filter()
                ->toArray();

            $groupCount = $classGroups->count();
            $startIdx = $groupPositions[$targetClassId] % $groupCount;
            $selectedGroup = null;
            $selectedIdx = $startIdx;

            for ($i = 0; $i < $groupCount; $i++) {
                $candidateIdx = ($startIdx + $i) % $groupCount;
                $candidateGroup = $classGroups[$candidateIdx];

                if (!in_array($candidateGroup->id, $assignedGroupIds, true)) {
                    $selectedGroup = $candidateGroup;
                    $selectedIdx = $candidateIdx;
                    break;
                }
            }

            if (!$selectedGroup) {
                $skipped++;

                if (!$noAvailableGroupWarned) {
                    $this->dispatch('show-toast', type: 'warning', message: 'Beberapa tanggal dilewati karena semua grup di kelas tersebut sudah dipakai di divisi lain pada hari yang sama.');
                    $noAvailableGroupWarned = true;
                }

                $current->addDay();
                continue;
            }

            // Kunci posisi ke grup yang dipilih agar konsisten selama siklus aktif.
            $groupPositions[$targetClassId] = $selectedIdx;

            if ($existing && !$this->overwriteExisting) {
                $skipped++;
                $current->addDay();
                continue;
            }

            if ($existing) {
                $existing->update([
                    'student_group_id' => $selectedGroup->id,
                    'shift_id'         => $this->shift_id,
                    'status'           => true,
                ]);
            } else {
                Schedules::create([
                    'type'             => 'production',
                    'date'             => $dateStr,
                    'shift_id'         => $this->shift_id,
                    'student_group_id' => $selectedGroup->id,
                    'division_id'      => $this->division_id,
                    'status'           => true,
                ]);
                $created++;
            }

            $current->addDay();
        }
    }

    /**
     * AUTO CASHIER dari CAFÉ & RESTO Production
     * Untuk setiap kelompok yang sedang production di Café & Resto,
     * generate rolling cashier harian dari siswa dalam kelompok tersebut
     */
    private function generateCashierFromCafeResto(Carbon $startDate, Carbon $endDate, &$created, &$skipped): void
    {
        $current = $startDate->copy();
        $selectedClassIds = collect($this->class_ids)
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        // Get semua kelompok dari kelas terpilih (kelompok bisa flexible diassign ke berbagai divisi)
        $allGroups = StudentGroups::where('status', true)
            ->whereIn('class_id', $selectedClassIds)
            ->orderBy('class_id')
            ->orderBy('name')
            ->get();

        if ($allGroups->isEmpty()) {
            return;
        }

        $groupsByClass = $allGroups->groupBy('class_id');
        $groupPositions = [];

        foreach ($selectedClassIds as $classId) {
            $classGroups = $groupsByClass->get($classId);
            $groupPositions[$classId] = 0;

            if (!$classGroups || $classGroups->isEmpty()) {
                continue;
            }

            $lastClassProd = Schedules::where('type', 'production')
                ->where('division_id', $this->division_id)
                ->where('shift_id', $this->shift_id)
                ->where('date', '<', $startDate->toDateString())
                ->whereIn('student_group_id', $classGroups->pluck('id'))
                ->orderBy('date', 'desc')
                ->first();

            if ($lastClassProd && $lastClassProd->student_group_id) {
                $lastIdx = $classGroups->search(fn($group) => $group->id == $lastClassProd->student_group_id);
                $groupPositions[$classId] = ($lastIdx === false ? 0 : $lastIdx + 1) % $classGroups->count();
            }
        }

        $cycleStartDate = $startDate->copy();
        $nextCycleDate = $cycleStartDate->copy()->addWeeks(3);
        $currentWeekKey = null;
        $weeklySlot = 0;

        while ($current <= $endDate) {
            if ($this->skipWeekends && $current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $weekKey = $current->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
            if ($currentWeekKey !== $weekKey) {
                $currentWeekKey = $weekKey;
                $weeklySlot = 0;
            }

            // Check if we need to rotate to next group (every 3 weeks)
            if ($current >= $nextCycleDate) {
                foreach ($selectedClassIds as $classId) {
                    $groupPositions[$classId]++;
                }
                $cycleStartDate = $current->copy();
                $nextCycleDate = $cycleStartDate->copy()->addWeeks(3);
            }

            if (count($selectedClassIds) === 2 && $current->isFriday()) {
                $current->addDay();
                continue;
            }

            if (count($selectedClassIds) > 1) {
                $classIndex = intdiv($weeklySlot, 2) % count($selectedClassIds);
                $targetClassId = $selectedClassIds[$classIndex];
            } else {
                $targetClassId = $selectedClassIds[0];
            }

            $weeklySlot++;

            $classGroups = $groupsByClass->get($targetClassId);
            if (!$classGroups || $classGroups->isEmpty()) {
                $current->addDay();
                continue;
            }

            $currentGroup = $classGroups[$groupPositions[$targetClassId] % $classGroups->count()];
            
            // Get students from this group
            $students = $currentGroup->students()
                ->where('status', true)
                ->orderBy('name')
                ->get();

            if ($students->isEmpty()) {
                $current->addDay();
                continue;
            }

            // Rolling cashier harian dari siswa dalam kelompok
            $dateStr = $current->toDateString();
            $dayOfWeek = $current->dayOfWeek;
            $cashierPos = $dayOfWeek % $students->count();
            $cashierStudent = $students[$cashierPos];

            $existing = Schedules::where('date', $dateStr)
                ->where('type', 'cashier')
                ->where('shift_id', $this->shift_id)
                ->where('student_id', $cashierStudent->id)
                ->first();

            if ($existing && !$this->overwriteExisting) {
                $skipped++;
                $current->addDay();
                continue;
            }

            if ($existing) {
                $existing->update([
                    'student_id' => $cashierStudent->id,
                    'shift_id'   => $this->shift_id,
                    'class_id'   => $currentGroup->class_id,
                    'status'     => true,
                ]);
            } else {
                Schedules::create([
                    'type'       => 'cashier',
                    'date'       => $dateStr,
                    'shift_id'   => $this->shift_id,
                    'student_id' => $cashierStudent->id,
                    'class_id'   => $currentGroup->class_id,
                    'status'     => true,
                ]);
                $created++;
            }

            $current->addDay();
        }
    }

    public function render()
    {
        return view('livewire.admin.schedules.modals.auto-generate', [
            'shifts'    => Shift::where('status', true)->orderBy('name')->get(),
            'classes'   => Classes::where('status', true)->orderBy('name')->get(),
            'divisions' => Divisions::where('status', true)->orderBy('name')->get(),
        ]);
    }
}