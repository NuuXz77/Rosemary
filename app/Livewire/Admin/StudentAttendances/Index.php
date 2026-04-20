<?php

namespace App\Livewire\Admin\StudentAttendances;

use App\Models\Classes;
use App\Models\StudentAttendance;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Kehadiran Siswa')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterStatus = '';
    public ?int $filterClass = null;
    public string $filterDate = '';

    #[On('attendance-created')]
    #[On('attendance-updated')]
    #[On('attendance-deleted')]
    public function refreshData(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->filterDate = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterClass(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDate(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterDate = now()->toDateString();
        $this->filterClass = null;
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('schedules.edit') && !auth()->user()->can('schedules.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah data kehadiran.');
            return;
        }

        $this->dispatch('open-edit-attendance', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_student_attendance');
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('schedules.delete') && !auth()->user()->can('schedules.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus data kehadiran.');
            return;
        }

        $this->dispatch('open-delete-attendance', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_student_attendance');
    }

    public function render()
    {
        $attendances = StudentAttendance::query()
            ->with(['student.schoolClass', 'shift', 'schedule'])
            ->when($this->search, function ($query) {
                $query->whereHas('student', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('pin', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterClass, function ($q) {
                $q->whereHas('student', fn($sq) => $sq->where('class_id', $this->filterClass));
            })
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->orderBy('date', 'desc')
            ->orderBy('login_time', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.student-attendances.index', [
            'attendances' => $attendances,
            'classes' => Classes::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
