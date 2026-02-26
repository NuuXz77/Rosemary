<?php

namespace App\Livewire\Admin\StudentGroupMembers;

use App\Models\StudentGroupMembers;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $members = StudentGroupMembers::query()
            ->with(['studentGroup', 'student'])
            ->when($this->search, function ($query) {
                $query->whereHas('student', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('studentGroup', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'));
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.student-group-members.index', [
            'members' => $members,
        ]);
    }
}
