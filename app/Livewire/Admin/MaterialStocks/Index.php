<?php

namespace App\Livewire\Admin\MaterialStocks;

use App\Models\MaterialStocks;
use App\Models\Materials;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Stok Bahan Baku')]

    public string $search = '';
    public int $perPage = 10;
    public string $filterCategory = '';
    public string $filterStockStatus = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterCategory(): void { $this->resetPage(); }
    public function updatingFilterStockStatus(): void { $this->resetPage(); }

    public function openAdjustment(int $id): void
    {
        $this->dispatch('open-adjust-material', id: $id);
    }

    #[On('material-stock-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $stocks = MaterialStocks::query()
            ->with(['material.category', 'material.unit'])
            ->whereHas('material', function ($q) {
                $q->when($this->search, fn($s) => $s->where('name', 'like', '%' . $this->search . '%'))
                    ->when($this->filterCategory, fn($s) => $s->where('category_id', $this->filterCategory));
            })
            ->when($this->filterStockStatus === 'low', function ($q) {
                $q->whereHas('material', fn($m) => $m->whereColumn('material_stocks.qty_available', '<=', 'materials.minimum_stock'));
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.material-stocks.index', [
            'stocks'     => $stocks,
            'categories' => \App\Models\Categories::where('type', 'material')->orderBy('name')->get(),
        ]);
    }
}
