<?php

namespace App\Livewire\Admin\Schedules;

use App\Models\Schedules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Schedules')]
    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $schedules = Schedules::query()
            ->with(['shift', 'studentGroup', 'division'])
            ->when($this->search, function ($query) {
                $query->whereHas('studentGroup', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('division', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderByDesc('date')
            ->paginate($this->perPage);

        return view('livewire.admin.schedules.index', [
            'schedules' => $schedules,
        ]);
    }
}
