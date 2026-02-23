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

    #[Title('Product Materials')]
    public string $search = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $productMaterials = ProductMaterials::query()
            ->with(['product', 'material'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('material', fn ($innerQuery) => $innerQuery->where('name', 'like', '%' . $this->search . '%'));
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.product-materials.index', [
            'productMaterials' => $productMaterials,
        ]);
    }
}
