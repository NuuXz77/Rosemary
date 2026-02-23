<?php

namespace App\Livewire\Admin\Productions;

use App\Models\Productions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Productions')]
    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $productions = Productions::query()
            ->with(['product', 'studentGroup', 'shift', 'creator'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('studentGroup', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderByDesc('production_date')
            ->paginate($this->perPage);

        return view('livewire.admin.productions.index', [
            'productions' => $productions,
        ]);
    }
}
