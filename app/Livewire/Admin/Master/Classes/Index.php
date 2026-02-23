<?php

namespace App\Livewire\Admin\Master\Classes;

use App\Models\Classes;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Kelas')]

    public string $search = '';
    public int $perPage = 10;

    protected $listeners = [
        'class-created' => '$refresh',
        'class-updated' => '$refresh',
        'class-deleted' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $classes = Classes::query()
            ->withCount('students')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.master.classes.index', [
            'classes' => $classes,
        ]);
    }
}
