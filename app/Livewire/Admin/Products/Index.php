<?php

namespace App\Livewire\Admin\Products;

use App\Models\Products;
use App\Models\Categories;
use App\Models\Divisions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Manajemen Produk')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterCategory = '';
    public string $filterDivision = '';
    public string $filterStatus = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function mount()
    {
        if (!auth()->user()->can('products.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman ini.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }
    public function updatingFilterDivision(): void
    {
        $this->resetPage();
    }
    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        if (!auth()->user()->can('products.edit')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk mengedit produk.');
            return;
        }
        $this->dispatch('open-edit-product', id: $id);
    }

    public function confirmDelete(int $id): void
    {
        if (!auth()->user()->can('products.delete')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki izin untuk menghapus produk.');
            return;
        }
        $this->dispatch('open-delete-product', id: $id);
    }

    #[On('product-changed')]
    public function refreshList(): void
    {
        // Re-render handled by Livewire automatically
    }

    public function render()
    {
        $products = Products::query()
            ->with(['category', 'division', 'stock'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('barcode', 'like', '%' . $this->search . '%')
                        ->orWhereHas('category', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('division', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterDivision, fn($q) => $q->where('division_id', $this->filterDivision))
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', (bool) $this->filterStatus))
            ->when(
                $this->sortField === 'stock',
                fn($q) =>
                $q->orderByRaw('(SELECT qty_available FROM product_stocks WHERE product_id = products.id LIMIT 1) ' . $this->sortDirection)
            )
            ->when(
                $this->sortField !== 'stock',
                fn($q) =>
                $q->orderBy($this->sortField, $this->sortDirection)
            )
            ->paginate($this->perPage);

        $categories = Categories::where('type', 'product')->where('status', true)->orderBy('name')->get();
        $divisions = Divisions::where('status', true)->orderBy('name')->get();

        return view('livewire.admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'divisions' => $divisions,
        ]);
    }
}
