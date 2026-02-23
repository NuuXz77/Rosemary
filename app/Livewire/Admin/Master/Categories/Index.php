<?php

namespace App\Livewire\Admin\Master\Categories;

use App\Models\Categories;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Master Categories')]
    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $categories = Categories::query()
            ->when($this->search, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('type', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.master.categories.index', [
            'categories' => $categories,
        ]);
    }
}
