<?php

namespace App\Livewire\Admin\StudentGroupMembers;

use App\Models\Classes;
use App\Models\StudentGroupMembers;
use App\Models\StudentGroups;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Student Group Members')]
    public string $search = '';
    public int $perPage = 10;
    public string $filterGroup = '';
    public string $filterClass = '';

    protected $listeners = [
        'member-created' => '$refresh',
        'member-updated' => '$refresh',
        'member-deleted' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterGroup(): void
    {
        $this->resetPage();
    }

    public function updatingFilterClass(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterGroup = '';
        $this->filterClass = '';
        $this->resetPage();
    }

    public function edit($id): void
    {
        $this->dispatch('open-edit-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_member');
    }

    public function confirmDelete($id): void
    {
        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_member');
    }

    public function render()
    {
        $members = StudentGroupMembers::query()
            ->with(['studentGroup', 'student.schoolClass'])
            ->when($this->search, function ($query) {
                $query->whereHas('student', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('studentGroup', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterGroup, function ($query) {
                $query->where('student_group_id', $this->filterGroup);
            })
            ->when($this->filterClass, function ($query) {
                $query->whereHas('student', fn ($innerQuery) => $innerQuery->where('class_id', $this->filterClass));
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.student-group-members.index', [
            'members' => $members,
            'groups' => StudentGroups::where('status', true)->orderBy('name')->get(),
            'classes' => Classes::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
