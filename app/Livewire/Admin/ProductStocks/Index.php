<?php

namespace App\Livewire\Admin\ProductStocks;

use App\Models\ProductStocks;
use App\Models\Products;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Stok Produk Jadi')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterCategory = '';
    public string $filterDivision = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterCategory(): void { $this->resetPage(); }
    public function updatingFilterDivision(): void { $this->resetPage(); }

    public function openAdjustment(int $id): void
    {
        abort_unless(auth()->user()?->can('product-stocks.adjust'), 403);
        $this->dispatch('open-adjust-product', id: $id);
    }

    #[On('stock-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $stocks = ProductStocks::query()
            ->with(['product.category', 'product.division'])
            ->whereHas('product', function ($q) {
                $q->when($this->search, fn($s) => $s->where('name', 'like', '%' . $this->search . '%'))
                    ->when($this->filterCategory, fn($s) => $s->where('category_id', $this->filterCategory))
                    ->when($this->filterDivision, fn($s) => $s->where('division_id', $this->filterDivision));
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.product-stocks.index', [
            'stocks' => $stocks,
            'categories' => \App\Models\Categories::where('type', 'product')->get(),
            'divisions' => \App\Models\Divisions::all(),
            'canAdjustProductStock' => auth()->user()?->can('product-stocks.adjust') ?? false,
        ]);
    }
}
