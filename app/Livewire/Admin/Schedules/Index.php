<?php

namespace App\Livewire\Admin\Schedules;

use App\Models\Classes;
use App\Models\Divisions;
use App\Models\Schedules;
use App\Models\Shift;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Jadwal Siswa')]
class Index extends Component
{
    public int $selectedMonth;
    public int $selectedYear;
    public string $filterType     = '';
    public string $filterClass    = '';
    public string $filterDivision = '';
    public string $filterShift    = '';

    public function mount(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear  = now()->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth)->subMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear  = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth)->addMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear  = $date->year;
    }

    public function today(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear  = now()->year;
    }

    public function resetFilters(): void
    {
        $this->filterType     = '';
        $this->filterClass    = '';
        $this->filterDivision = '';
        $this->filterShift    = '';
    }

    public function updated($property): void
    {
        // Reset applicable filters when type changes
        if ($property === 'filterType') {
            // Keep filterShift, but reset class and division when type changes
            $this->filterClass    = '';
            $this->filterDivision = '';
        }
    }

    #[On('schedule-changed')]
    public function scheduleChanged(): void
    {
        // triggers re-render to refresh calendar data
    }

    public function openAutoGenerate(): void
    {
        $this->dispatch('open-autogenerate-schedule', month: $this->selectedMonth, year: $this->selectedYear);
    }

    public function openCreateForDate(string $date): void
    {
        $this->dispatch('open-create-schedule', date: $date);
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('open-edit-schedule', id: $id);
    }

    public function openDelete(int $id): void
    {
        $this->dispatch('open-delete-schedule', id: $id);
    }

    public function openBulkDelete(): void
    {
        $this->dispatch('open-bulk-delete-schedule');
    }

    public function openMarkUnavailable(int $id): void
    {
        $this->dispatch('open-mark-unavailable-schedule', id: $id);
    }

    public function render()
    {
        $start           = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $end             = $start->copy()->endOfMonth();
        $startOfCalendar = $start->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar   = $end->copy()->endOfWeek(Carbon::SUNDAY);

        // Only fetch schedules if mandatory filters are satisfied
        $canFetchSchedules = !empty($this->filterType);

        $schedules = $canFetchSchedules
            ? Schedules::query()
                ->with(['shift', 'student.schoolClass', 'studentGroup.schoolClass', 'division'])
                ->where('status', true)
                ->whereBetween('date', [$startOfCalendar->toDateString(), $endOfCalendar->toDateString()])
                ->where('type', $this->filterType)
                ->when($this->filterType === 'cashier', function ($q) {
                    $q->where(function ($query) {
                        $query->whereNull('absence_type')
                            ->orWhere('absence_type', '!=', Schedules::ABSENCE_RESCHEDULED);
                    });
                })
                ->when($this->filterClass, function ($q) {
                    // Filter by class: cashier uses class_id, production uses studentGroup.class_id
                    if ($this->filterType === 'cashier') {
                        $q->where('class_id', $this->filterClass);
                    } else {
                        $q->whereHas('studentGroup', fn($q2) => $q2->where('class_id', $this->filterClass));
                    }
                })
                ->when($this->filterDivision, fn ($q) => $q->where('division_id', $this->filterDivision))
                ->when($this->filterShift,    fn ($q) => $q->where('shift_id', $this->filterShift))
                ->get()
            : collect();

        $calendarDays = [];
        $d = $startOfCalendar->copy();
        while ($d <= $endOfCalendar) {
            $calendarDays[] = $d->copy();
            $d->addDay();
        }

        return view('livewire.admin.schedules.index', [
            'schedules'          => $schedules,
            'classes'            => Classes::orderBy('name')->get(),
            'divisions'          => Divisions::orderBy('type')->orderBy('name')->get(),
            'divisionsCashier'   => Divisions::where('type', 'cashier')->orderBy('name')->get(),
            'divisionsProduction'=> Divisions::where('type', 'production')->orderBy('name')->get(),
            'shifts'             => Shift::orderBy('name')->get(),
            'calendarDays'       => $calendarDays,
        ]);
    }
}
