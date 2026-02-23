<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\Sales;
use App\Models\SaleItems;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Laporan Penjualan — RoseMarry')]

    public $startDate;
    public $endDate;
    public $filterPayment = '';
    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'filterPayment', 'search'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Sales::query()
            ->with(['customer', 'cashier', 'shift'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->startDate, $this->endDate])
            ->when($this->filterPayment, fn($q) => $q->where('payment_method', $this->filterPayment))
            ->when($this->search, function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'));
            });

        // Summary Stats
        $summary = [
            'total_sales' => (clone $query)->where('status', 'paid')->sum('total_amount'),
            'total_count' => (clone $query)->count(),
            'paid_count' => (clone $query)->where('status', 'paid')->count(),
            'cancelled_count' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        $sales = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.admin.reports.sales.index', [
            'sales' => $sales,
            'summary' => $summary
        ]);
    }
}
