<?php

namespace App\Livewire\Admin\ProductMaterials;

use App\Models\ProductMaterials;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Manajemen Resep (BOM)')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;
    public string $filterSort = 'newest';

    protected $listeners = [
        'recipe-created' => '$refresh',
        'recipe-updated' => '$refresh',
        'recipe-deleted' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterSort = 'newest';
        $this->resetPage();
    }

    public function edit($id)
    {
        $this->dispatch('open-edit-modal', id: $id);
        $this->dispatch('open-modal', id: 'modal_edit_recipe');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
        $this->dispatch('open-modal', id: 'modal_delete_recipe');
    }

    public function render()
    {
        $recipes = ProductMaterials::query()
            ->with(['product.category', 'material.category', 'material.unit'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                      ->orWhereHas('material', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy('created_at', $this->filterSort === 'oldest' ? 'asc' : 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.product-materials.index', [
            'recipes' => $recipes,
        ]);
    }
}
