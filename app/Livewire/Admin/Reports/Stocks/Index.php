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

    #[Title('Laporan Pergerakan Stok')]
    public $startDate;
    public $endDate;
    public $search = '';
    public $perPage = 15;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'search'])) {
            $this->resetPage();
        }
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StocksExport($this->startDate, $this->endDate, $this->search), 
            'laporan_pergerakan_stok_'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function render()
    {
        $stocks = ProductStocks::query()
            ->with(['product'])
            ->when($this->search, fn($q) => $q->whereHas('product', fn($s) => $s->where('name', 'like', '%' . $this->search . '%')))
            ->orderBy('qty_available', 'desc')
            ->paginate($this->perPage);

        // Map reconciliation data for the current page items
        $stocks->getCollection()->transform(function($stock) {
            $start = \Carbon\Carbon::parse($this->startDate)->startOfDay();
            $end = \Carbon\Carbon::parse($this->endDate)->endOfDay();
            
            // 1. Current Balance
            $currentBalance = $stock->qty_available ?? 0;
            
            // 2. Movements AFTER the selected period (to back-calculate)
            $futureMovements = \App\Models\ProductStockLogs::where('product_id', $stock->product_id)
                ->where('created_at', '>', $end)
                ->sum('qty');
            
            // 3. Balance at end of period
            $endingBalance = $currentBalance - $futureMovements;
            
            // 4. Movements WITHIN period
            $logsInPeriod = \App\Models\ProductStockLogs::where('product_id', $stock->product_id)
                ->whereBetween('created_at', [$start, $end])
                ->get();
            
            $produced = $logsInPeriod->where('type', 'in')->sum('qty');
            $out = $logsInPeriod->where('type', 'out')->sum('qty'); // Already negative
            
            // 5. Balance at start of period
            $startingBalance = $endingBalance - $produced - $out;

            $stock->starting_qty = $startingBalance;
            $stock->qty_in = $produced;
            $stock->qty_out = abs($out);
            $stock->ending_qty = $endingBalance;
            $stock->asset_value = ($stock->product->cost_price ?? 0) * $currentBalance;

            return $stock;
        });

        return view('livewire.admin.reports.stocks.index', [
            'stocks' => $stocks,
        ]);
    }
}
