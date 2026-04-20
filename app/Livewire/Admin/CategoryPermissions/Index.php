<?php

namespace App\Livewire\Admin\CategoryPermissions;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\CategoryPermissions;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Kategori Permission Management')]
    
    public $search = '';
    public $perPage = 10;
    public $sortField = 'order';
    public $sortDirection = 'asc';
    public string $filterUsage = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterUsage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterUsage = '';
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function create()
    {
        $this->dispatch('open-create-modal');
    }

    public function edit($id)
    {
        $this->dispatch('open-edit-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_category');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_category');
    }

    protected $listeners = [
        'category-created' => 'refresh',
        'category-updated' => 'refresh',
        'category-deleted' => 'refresh',
    ];

    public function render()
    {
        $categories = CategoryPermissions::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterUsage === 'used', fn($query) => $query->has('permissions'))
            ->when($this->filterUsage === 'unused', fn($query) => $query->doesntHave('permissions'))
            ->withCount('permissions')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.category-permissions.index', [
            'categories' => $categories,
        ]);
    }
}
