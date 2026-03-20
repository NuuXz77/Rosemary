<?php

namespace App\Livewire\Admin\StudentGroups;

use App\Models\Classes;
use App\Models\StudentGroups;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Kelompok Siswa')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;
    public string $filterClass = '';
    public string $filterStatus = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    protected $listeners = [
        'group-created' => '$refresh',
        'group-updated' => '$refresh',
        'group-deleted' => '$refresh',
        'group-members-updated' => '$refresh',
    ];

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
        $this->filterClass = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $allowedSortFields = ['created_at', 'name', 'students_count', 'status'];

        if (!in_array($field, $allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function edit($id)
    {
        $this->dispatch('open-edit-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_group');
    }

    public function manageMembers($id)
    {
        $this->dispatch('open-detail-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_detail_group');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_group');
    }

    public function render()
    {
        $groups = StudentGroups::query()
            ->with(['schoolClass'])
            ->withCount('students')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('schoolClass', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterClass, function ($query) {
                $query->where('class_id', $this->filterClass);
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('status', $this->filterStatus === 'active');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.student-groups.index', [
            'groups' => $groups,
            'classes' => Classes::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
