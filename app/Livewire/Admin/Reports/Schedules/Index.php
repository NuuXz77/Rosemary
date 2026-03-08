<?php

namespace App\Livewire\Admin\Reports\Schedules;

use App\Models\Schedules;
use App\Models\Shift;
use App\Models\StudentGroups;
use App\Models\Divisions;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Laporan Jadwal — RoseMarry')]

    public $startDate;
    public $endDate;
    public $search = '';
    public $filterShift = '';
    public $filterGroup = '';
    public $filterDivision = '';
    public $perPage = 10;

    public function mount()
    {
        // Default: minggu ini
        $this->startDate = now()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->endOfWeek()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'search', 'filterShift', 'filterGroup', 'filterDivision'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        // Base query
        $baseQuery = Schedules::query()
            ->with(['shift', 'studentGroup', 'division'])
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->when($this->filterShift, fn($q) => $q->where('shift_id', $this->filterShift))
            ->when($this->filterGroup, fn($q) => $q->where('student_group_id', $this->filterGroup))
            ->when($this->filterDivision, fn($q) => $q->where('division_id', $this->filterDivision))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('studentGroup', fn($g) => $g->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('division', fn($d) => $d->where('name', 'like', '%' . $this->search . '%'));
                });
            });

        // Summary
        $summary = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', true)->count(),
            'inactive' => (clone $baseQuery)->where('status', false)->count(),
        ];

        // Paginated table
        $schedules = (clone $baseQuery)->orderBy('date', 'desc')->paginate($this->perPage);

        // === Matrix jadwal mingguan ===
        $period = CarbonPeriod::create($this->startDate, $this->endDate);
        $days = collect();
        foreach ($period as $date) {
            $days->push($date->copy());
        }
        // Batasi max 14 hari untuk matrix
        $matrixDays = $days->take(14);

        $matrixSchedules = Schedules::with(['shift', 'studentGroup', 'division'])
            ->whereBetween('date', [$matrixDays->first()->format('Y-m-d'), $matrixDays->last()->format('Y-m-d')])
            ->where('status', true)
            ->when($this->filterShift, fn($q) => $q->where('shift_id', $this->filterShift))
            ->when($this->filterDivision, fn($q) => $q->where('division_id', $this->filterDivision))
            ->get()
            ->groupBy(function ($schedule) {
                return $schedule->student_group_id;
            });

        // Grup yang punya jadwal dalam range ini
        $matrixGroups = StudentGroups::whereIn('id', $matrixSchedules->keys())->orderBy('name')->get();

        return view('livewire.admin.reports.schedules.index', [
            'schedules' => $schedules,
            'summary' => $summary,
            'matrixDays' => $matrixDays,
            'matrixSchedules' => $matrixSchedules,
            'matrixGroups' => $matrixGroups,
            'shifts' => Shift::where('status', true)->get(),
            'groups' => StudentGroups::where('status', true)->get(),
            'divisions' => Divisions::where('status', true)->get(),
        ]);
    }
}
