<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Sales;
use App\Models\MaterialStocks;
use App\Models\MaterialStockLogs;
use App\Models\MaterialWastes;
use App\Models\ProductStocks;
use App\Models\ProductWastes;
use App\Models\Productions;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Title('Dashboard — RoseMarry')]

    public string $period = 'month';
    public int $chartDays = 7;
    public string $dashboardRole = 'admin';

    public function mount()
    {
        $user = auth()->user();

        if ($user->hasRole('Cashier')) {
            return $this->redirect(route('kasir.pos'), navigate: true);
        }

        if ($user->hasRole('Production')) {
            $this->dashboardRole = 'production';
            return;
        }

        if ($user->hasRole('Inventory')) {
            $this->dashboardRole = 'inventory';
            return;
        }

        $this->dashboardRole = 'admin';
    }

    private function getPeriodRange(): array
    {
        return match ($this->period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function getPreviousPeriodRange(): array
    {
        return match ($this->period) {
            'today' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
        };
    }

    private function percentChange($current, $previous): ?float
    {
        if ($previous == 0)
            return $current > 0 ? 100 : null;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function buildProductionDashboardData($start, $end, $prevStart, $prevEnd): array
    {
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();
        $prevStartDate = $prevStart->toDateString();
        $prevEndDate = $prevEnd->toDateString();

        $periodProd = Productions::whereBetween('production_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();

        $periodProdQty = Productions::whereBetween('production_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum(DB::raw('COALESCE(actual_qty, qty_produced)'));

        $prevProd = Productions::whereBetween('production_date', [$prevStartDate, $prevEndDate])
            ->where('status', 'completed')
            ->count();

        $draftProd = Productions::where('status', 'draft')->count();

        $productWasteQty = ProductWastes::whereBetween('waste_date', [$startDate, $endDate])->sum('qty');

        $lowStockProducts = ProductStocks::where('qty_available', '<=', 5)->count();

        $productionTrend = Productions::select(
            DB::raw('DATE(production_date) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->where('status', 'completed')
            ->where('production_date', '>=', now()->subDays($this->chartDays)->toDateString())
            ->groupBy(DB::raw('DATE(production_date)'))
            ->orderBy('date')
            ->get();

        return [
            'periodProd' => $periodProd,
            'periodProdQty' => $periodProdQty,
            'prodChange' => $this->percentChange($periodProd, $prevProd),
            'draftProd' => $draftProd,
            'productWasteQty' => $productWasteQty,
            'lowStockProducts' => $lowStockProducts,
            'productionTrend' => $productionTrend,
        ];
    }

    private function buildInventoryDashboardData($start, $end, $prevStart, $prevEnd): array
    {
        $lowStockMaterials = MaterialStocks::whereHas('material', function ($q) {
            $q->whereColumn('material_stocks.qty_available', '<=', 'materials.minimum_stock');
        })->count();

        $totalMaterialItems = MaterialStocks::count();
        $totalMaterialQty = MaterialStocks::sum('qty_available');

        $incomingQty = MaterialStockLogs::whereBetween('created_at', [$start, $end])
            ->where('type', 'in')
            ->sum(DB::raw('ABS(qty)'));

        $outgoingQty = MaterialStockLogs::whereBetween('created_at', [$start, $end])
            ->where('type', 'out')
            ->sum(DB::raw('ABS(qty)'));

        $prevIncomingQty = MaterialStockLogs::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('type', 'in')
            ->sum(DB::raw('ABS(qty)'));

        $prevOutgoingQty = MaterialStockLogs::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('type', 'out')
            ->sum(DB::raw('ABS(qty)'));

        $materialWasteQty = MaterialWastes::whereBetween('waste_date', [$start->toDateString(), $end->toDateString()])->sum('qty');

        $inventoryTrend = MaterialStockLogs::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw("SUM(CASE WHEN type = 'in' THEN ABS(qty) ELSE 0 END) as total_in"),
            DB::raw("SUM(CASE WHEN type = 'out' THEN ABS(qty) ELSE 0 END) as total_out")
        )
            ->where('created_at', '>=', now()->subDays($this->chartDays)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return [
            'lowStockMaterials' => $lowStockMaterials,
            'totalMaterialItems' => $totalMaterialItems,
            'totalMaterialQty' => $totalMaterialQty,
            'incomingQty' => $incomingQty,
            'outgoingQty' => $outgoingQty,
            'incomingChange' => $this->percentChange($incomingQty, $prevIncomingQty),
            'outgoingChange' => $this->percentChange($outgoingQty, $prevOutgoingQty),
            'materialWasteQty' => $materialWasteQty,
            'inventoryTrend' => $inventoryTrend,
        ];
    }

    public function render()
    {
        [$start, $end] = $this->getPeriodRange();
        [$prevStart, $prevEnd] = $this->getPreviousPeriodRange();

        if ($this->dashboardRole === 'production') {
            return view('livewire.admin.dashboard.index', array_merge([
                'period' => $this->period,
                'chartDays' => $this->chartDays,
                'dashboardRole' => $this->dashboardRole,
            ], $this->buildProductionDashboardData($start, $end, $prevStart, $prevEnd)));
        }

        if ($this->dashboardRole === 'inventory') {
            return view('livewire.admin.dashboard.index', array_merge([
                'period' => $this->period,
                'chartDays' => $this->chartDays,
                'dashboardRole' => $this->dashboardRole,
            ], $this->buildInventoryDashboardData($start, $end, $prevStart, $prevEnd)));
        }

        // === SALES ===
        $periodSales = Sales::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')->sum('total_amount');
        $prevSales = Sales::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'paid')->sum('total_amount');

        $periodTx = Sales::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')->count();
        $prevTx = Sales::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'paid')->count();

        // === LOW STOCK ===
        $lowStockMaterials = MaterialStocks::whereHas('material', function ($q) {
            $q->whereColumn('material_stocks.qty_available', '<=', 'materials.minimum_stock');
        })->count();

        $lowStockItems = MaterialStocks::with(['material', 'material.unit'])
            ->whereHas('material', function ($q) {
                $q->whereColumn('material_stocks.qty_available', '<=', 'materials.minimum_stock');
            })->limit(5)->get();

        // === LOW STOCK PRODUCTS (threshold: <= 5 pcs) ===
        $lowStockProducts = ProductStocks::where('qty_available', '<=', 5)->count();

        $lowStockProductItems = ProductStocks::with(['product'])
            ->where('qty_available', '<=', 5)
            ->orderBy('qty_available', 'asc')
            ->limit(5)->get();

        // === PRODUCTIONS ===
        $sDate = $start->toDateString();
        $eDate = $end->toDateString();
        $pSDate = $prevStart->toDateString();
        $pEDate = $prevEnd->toDateString();

        $periodProd = Productions::whereBetween('production_date', [$sDate, $eDate])
            ->where('status', 'completed')->count();
        $periodProdQty = Productions::whereBetween('production_date', [$sDate, $eDate])
            ->where('status', 'completed')->sum('qty_produced');
        $prevProd = Productions::whereBetween('production_date', [$pSDate, $pEDate])
            ->where('status', 'completed')->count();

        // === RECENT SALES ===
        $recentSales = Sales::with(['customer', 'cashier'])
            ->orderByDesc('created_at')->limit(5)->get();

        // === TOP PRODUCTS (fixed: filter cancelled + period) ===
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select('products.name', DB::raw('SUM(sale_items.qty) as total_qty'), DB::raw('SUM(sale_items.subtotal) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')->limit(5)->get();

        // === SALES TREND CHART ===
        $salesTrend = Sales::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as total'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays($this->chartDays))
            ->where('status', 'paid')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')->get();

        // === PAYMENT BREAKDOWN ===
        $paymentBreakdown = Sales::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->groupBy('payment_method')->get()->keyBy('payment_method');

        return view('livewire.admin.dashboard.index', [
            'dashboardRole' => $this->dashboardRole,
            'period' => $this->period,
            'chartDays' => $this->chartDays,
            'periodSales' => $periodSales,
            'salesChange' => $this->percentChange($periodSales, $prevSales),
            'periodTx' => $periodTx,
            'txChange' => $this->percentChange($periodTx, $prevTx),
            'lowStockMaterials' => $lowStockMaterials,
            'lowStockItems' => $lowStockItems,
            'lowStockProducts' => $lowStockProducts,
            'lowStockProductItems' => $lowStockProductItems,
            'periodProd' => $periodProd,
            'periodProdQty' => $periodProdQty,
            'prodChange' => $this->percentChange($periodProd, $prevProd),
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
            'salesTrend' => $salesTrend,
            'paymentBreakdown' => $paymentBreakdown,
        ]);
    }
}
