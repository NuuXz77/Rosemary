<?php

namespace App\Livewire\Admin\Students;

use App\Models\Students;
use App\Models\Classes;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Data Siswa')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;
    public ?int $filterClass = null;
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterClass(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterClass = null;
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function edit($id)
    {
        $this->dispatch('open-edit-student', id: $id);
    }

    public function confirmDelete($id)
    {
        $this->dispatch('open-delete-student', id: $id);
    }

    #[On('student-changed')]
    public function refreshData(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $students = Students::query()
            ->with(['schoolClass'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('pin', 'like', '%' . $this->search . '%')
                    ->orWhereHas('schoolClass', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterClass, function ($query) {
                $query->where('class_id', $this->filterClass);
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('status', $this->filterStatus === 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.students.index', [
            'students' => $students,
            'classes' => Classes::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
