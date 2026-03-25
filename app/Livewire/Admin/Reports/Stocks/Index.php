<?php

namespace App\Livewire\Admin\Reports\Stocks;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\ProductStocks;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Reports - Stocks')]
    public $search = '';
    public $perPage = 15;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StocksExport($this->search), 
            'laporan_stok_'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function render()
    {
        $stocks = ProductStocks::query()
            ->when($this->search, fn($q) => $q->whereHas('product', fn($s) => $s->where('name', 'like', '%' . $this->search . '%')))
            ->orderBy('qty_available', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.reports.stocks.index', [
            'stocks' => $stocks,
        ]);
    }
}
