<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\Sales;
use App\Models\Shift;
use App\Models\Students;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Laporan Penjualan')]

    public $startDate;
    public $endDate;
    public $filterPayment = '';
    public $filterShift = '';
    public $filterCashier = '';
    public $filterStatus = '';
    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'filterPayment', 'filterShift', 'filterCashier', 'filterStatus', 'search'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->filterPayment = '';
        $this->filterShift = '';
        $this->filterCashier = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SalesExport($this->startDate, $this->endDate, $this->filterPayment, $this->filterShift, $this->filterCashier, $this->filterStatus, $this->search), 
            'laporan_penjualan_'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function render()
    {
        // Base query (tanpa filter status — supaya summary bisa hitung paid+cancelled)
        $baseQuery = Sales::query()
            ->with(['customer', 'cashier', 'shift', 'items.product.materials'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->startDate, $this->endDate])
            ->when($this->filterPayment, fn($q) => $q->where('payment_method', $this->filterPayment))
            ->when($this->filterShift, fn($q) => $q->where('shift_id', $this->filterShift))
            ->when($this->filterCashier, fn($q) => $q->where('cashier_student_id', $this->filterCashier))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'));
                });
            });

        // Hitung HPP Total untuk Summary secara efisien
        $paidSales = (clone $baseQuery)->where('status', 'paid')
            ->with(['items.product.materials']) // Eager load untuk hitung HPP
            ->get();
            
        $totalHpp = 0;
        foreach($paidSales as $sale) {
            foreach($sale->items as $item) {
                // Gunakan harga modal dari resep
                if($item->product) {
                    $totalHpp += $item->product->cost_price * $item->qty;
                }
            }
        }

        // Summary dari base query (sebelum filter status)
        $totalSales = $paidSales->sum(fn($s) => $s->total_amount - $s->tax_amount);

        $summary = [
            'total_sales' => $totalSales, // Gross Sales (now same as Net since no tax)
            'total_hpp' => $totalHpp,
            'total_profit' => $totalSales - $totalHpp, // Laba Kotor (Total Sales - HPP)
            'total_count' => (clone $baseQuery)->count(),
            'paid_count' => $paidSales->count(),
            'cancelled_count' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];
        $summary['avg_per_sale'] = $summary['paid_count'] > 0
            ? round($summary['total_sales'] / $summary['paid_count'])
            : 0;

        // Full query (dengan filter status) untuk tabel
        $query = clone $baseQuery;
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }
        $sales = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        // Append HPP & Profit to each sale object for list display
        $sales->getCollection()->transform(function($sale) {
            if($sale->status == 'paid') {
                $saleHpp = 0;
                foreach($sale->items as $item) {
                    $saleHpp += ($item->product->cost_price ?? 0) * $item->qty;
                }
                $sale->total_hpp = $saleHpp;
                $sale->total_profit = ($sale->total_amount - $sale->tax_amount) - $saleHpp;
            } else {
                $sale->total_hpp = 0;
                $sale->total_profit = 0;
            }
            return $sale;
        });

        // Daily sales chart
        $dailySales = Sales::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount - tax_amount) as total'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->startDate, $this->endDate])
            ->where('status', 'paid')
            ->when($this->filterPayment, fn($q) => $q->where('payment_method', $this->filterPayment))
            ->when($this->filterShift, fn($q) => $q->where('shift_id', $this->filterShift))
            ->when($this->filterCashier, fn($q) => $q->where('cashier_student_id', $this->filterCashier))
            ->groupBy('date')->orderBy('date')->get();

        // Top products in period
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'paid')
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$this->startDate, $this->endDate])
            ->when($this->filterPayment, fn($q) => $q->where('sales.payment_method', $this->filterPayment))
            ->when($this->filterShift, fn($q) => $q->where('sales.shift_id', $this->filterShift))
            ->when($this->filterCashier, fn($q) => $q->where('sales.cashier_student_id', $this->filterCashier))
            ->select('products.name', DB::raw('SUM(sale_items.qty) as total_qty'), DB::raw('SUM(sale_items.subtotal) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')->limit(5)->get();

        return view('livewire.admin.reports.sales.index', [
            'sales' => $sales,
            'summary' => $summary,
            'dailySales' => $dailySales,
            'topProducts' => $topProducts,
            'shifts' => Shift::where('status', true)->get(),
            'students' => Students::where('status', true)->get(),
        ]);
    }
}
