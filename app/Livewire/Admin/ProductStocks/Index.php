<?php

namespace App\Livewire\Admin\ProductStocks;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\ProductStocks;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Product Stocks')]
    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        if (!auth()->user()->can('product-stocks.view') && !auth()->user()->can('product-stocks.manage')) {
            abort(403);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $stocks = ProductStocks::query()
            ->when($this->search, fn($q) => $q->whereHas('product', fn($s) => $s->where('name', 'like', '%'.$this->search.'%')))
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.product-stocks.index', [
            'stocks' => $stocks,
        ]);
    }
}
