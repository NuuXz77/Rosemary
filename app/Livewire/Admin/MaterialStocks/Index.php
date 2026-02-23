<?php

namespace App\Livewire\Admin\MaterialStocks;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\MaterialStocks;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Material Stocks')]
    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        if (!auth()->user()->can('material-stocks.view') && !auth()->user()->can('material-stocks.manage')) {
            abort(403);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $stocks = MaterialStocks::query()
            ->when($this->search, fn($q) => $q->whereHas('material', fn($s) => $s->where('name', 'like', '%'.$this->search.'%')))
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.material-stocks.index', [
            'stocks' => $stocks,
        ]);
    }
}
