<?php

namespace App\Livewire\Admin\Students;

use App\Models\Students;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Students')]
    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $students = Students::query()
            ->with('class')
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('pin', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.students.index', [
            'students' => $students,
        ]);
    }
}
