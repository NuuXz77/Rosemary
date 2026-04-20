<?php

namespace App\Livewire\Admin\StudentGroupAttendances;

use App\Models\Classes;
use App\Models\StudentGroupAttendance;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Kehadiran Grup')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterStatus = '';
    public ?int $filterClass = null;
    public string $filterDate = '';

    #[On('group-attendance-created')]
    #[On('group-attendance-updated')]
    #[On('group-attendance-deleted')]
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
        if (!auth()->user()->can('student-group-attendances.edit') && !auth()->user()->can('student-group-attendances.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengubah data kehadiran grup.');
            return;
        }

        $this->dispatch('open-edit-group-attendance', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_student_group_attendance');
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('student-group-attendances.delete') && !auth()->user()->can('student-group-attendances.manage')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus data kehadiran grup.');
            return;
        }

        $this->dispatch('open-delete-group-attendance', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_student_group_attendance');
    }

    public function render()
    {
        $attendances = StudentGroupAttendance::query()
            ->with(['studentGroup.schoolClass', 'shift', 'schedule'])
            ->when($this->search, function ($query) {
                $query->whereHas('studentGroup', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('group_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterClass, function ($q) {
                $q->whereHas('studentGroup', fn($sq) => $sq->where('class_id', $this->filterClass));
            })
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->orderBy('date', 'desc')
            ->orderBy('login_time', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.student-group-attendances.index', [
            'attendances' => $attendances,
            'classes' => Classes::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
