<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Sales;
use App\Models\MaterialStocks;
use App\Models\ProductStocks;
use App\Models\Productions;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Title('Dashboard — RoseMarry')]

    public function mount()
    {
        // Auto-redirect non-admin users to their relevant areas
        if (auth()->user()->hasRole('Production')) {
            return $this->redirect('/productions', navigate: true);
        } elseif (auth()->user()->hasRole('Inventory')) {
            return $this->redirect('/material-stocks', navigate: true);
        } elseif (auth()->user()->hasRole('Cashier')) {
            return $this->redirect('/sales/pos', navigate: true);
        }
    }

    public function render()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // 1. Calculations
        $todaySales = Sales::where('created_at', '>=', $today)
            ->where('status', 'paid')
            ->sum('total_amount');

        $monthSales = Sales::where('created_at', '>=', $thisMonth)
            ->where('status', 'paid')
            ->sum('total_amount');

        $lowStockMaterials = MaterialStocks::whereHas('material', function ($q) {
            $q->whereColumn('material_stocks.qty_available', '<=', 'materials.minimum_stock');
        })->count();

        $todayProductions = Productions::where('production_date', $today->toDateString())->count();

        // 2. Recent Transactions
        $recentSales = Sales::with(['customer', 'cashier'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // 3. Top Products (By Quantity Sold)
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(sale_items.qty) as total_qty'), DB::raw('SUM(sale_items.subtotal) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 4. Monthly Sales Data (for simple chart)
        $salesTrend = Sales::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->where('status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('livewire.admin.dashboard.index', [
            'todaySales' => $todaySales,
            'monthSales' => $monthSales,
            'lowStockMaterials' => $lowStockMaterials,
            'todayProductions' => $todayProductions,
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
            'salesTrend' => $salesTrend,
        ]);
    }
}
