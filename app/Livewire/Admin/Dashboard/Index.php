<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Sales;
use App\Models\MaterialStocks;
use App\Models\ProductStocks;
use App\Models\Productions;
use App\Models\StudentGroups;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
// use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    // #[Title('Dashboard')]

    public string $period = 'month';
    public int $chartDays = 7;
    public string $salesChartScope = 'daily';
    public string $dashboardRole = 'admin';
    public ?int $productionGroupId = null;
    public ?string $productionGroupName = null;

    public function mount()
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['Cashier', 'cashier'])) {
            $this->dashboardRole = 'cashier';
            return;
        }

        if ($user->hasRole('Production')) {
            $this->dashboardRole = 'production';
            $group = StudentGroups::query()
                ->where('group_code', $user->username)
                ->where('status', true)
                ->first();

            $this->productionGroupId = $group?->id;
            $this->productionGroupName = $group?->name;
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
        if ($previous <= 0)
            return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function updatedChartDays($value): void
    {
        $this->chartDays = (int) $value;
    }

    public function updatedSalesChartScope($value): void
    {
        $allowedScopes = ['daily', 'weekly', 'monthly', 'yearly'];
        $this->salesChartScope = in_array($value, $allowedScopes, true) ? $value : 'daily';
    }

    private function buildDailySalesTrend(): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $sales = Sales::where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->groupBy(fn($s) => $s->created_at->toDateString());

        $labels = [];
        $series = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $series[] = (float) ($sales->get($key)?->sum(fn($s) => $s->total_amount - $s->tax_amount) ?? 0);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function buildWeeklySalesTrend(): array
    {
        $start = now()->subMonth()->startOfDay();
        $end = now()->endOfDay();

        $dailyRows = Sales::selectRaw('DATE(created_at) as bucket, SUM(total_amount) as total')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('bucket')
            ->get();

        $bucketed = [];
        foreach ($dailyRows as $row) {
            $weekStart = Carbon::parse($row->bucket)->startOfWeek()->toDateString();
            $bucketed[$weekStart] = ($bucketed[$weekStart] ?? 0) + (float) $row->total;
        }

        $labels = [];
        $series = [];
        $cursor = $start->copy()->startOfWeek();

        while ($cursor->lte($end)) {
            $weekStart = $cursor->copy();
            $weekEnd = $cursor->copy()->endOfWeek();
            $key = $weekStart->toDateString();

            $labels[] = $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m');
            $series[] = (float) ($bucketed[$key] ?? 0);

            $cursor->addWeek();
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function buildMonthlySalesTrend(): array
    {
        $start = now()->startOfMonth()->subMonths(11);
        $end = now()->endOfMonth();

        $raw = Sales::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bucket, SUM(total_amount) as total")
            ->where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $labels = [];
        $series = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('M y');
            $series[] = (float) ($raw[$key] ?? 0);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function buildYearlySalesTrend(): array
    {
        $currentYear = now()->year;
        $startYear = $currentYear - 4;

        $raw = Sales::selectRaw('YEAR(created_at) as bucket, SUM(total_amount) as total')
            ->where('status', 'paid')
            ->whereYear('created_at', '>=', $startYear)
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $labels = [];
        $series = [];

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $labels[] = (string) $year;
            $series[] = (float) ($raw[$year] ?? 0);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function buildSalesTrendData(): array
    {
        return match ($this->salesChartScope) {
            'weekly' => $this->buildWeeklySalesTrend(),
            'monthly' => $this->buildMonthlySalesTrend(),
            'yearly' => $this->buildYearlySalesTrend(),
            default => $this->buildDailySalesTrend(),
        };
    }

    public function render()
    {
        [$start, $end] = $this->getPeriodRange();
        [$prevStart, $prevEnd] = $this->getPreviousPeriodRange();

        if ($this->dashboardRole === 'production') {
            return view('livewire.admin.dashboard.index', [
                'dashboardRole' => $this->dashboardRole,
                'productionGroupName' => $this->productionGroupName,
                'productionGroupId' => $this->productionGroupId,
            ]);
        }

        if ($this->dashboardRole === 'inventory') {
            return view('livewire.admin.dashboard.index', [
                'dashboardRole' => $this->dashboardRole,
            ]);
        }

        if ($this->dashboardRole === 'cashier') {
            return view('livewire.admin.dashboard.index', [
                'dashboardRole' => $this->dashboardRole,
            ]);
        }

        $sDate = $start->toDateString();
        $eDate = $end->toDateString();
        $pSDate = $prevStart->toDateString();
        $pEDate = $prevEnd->toDateString();

        // === SALES (NET) ===
        // Net Sales = Total - Tax
        $periodSales = Sales::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')->get()->sum(fn($s) => $s->total_amount - $s->tax_amount);
        $prevSales = Sales::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'paid')->get()->sum(fn($s) => $s->total_amount - $s->tax_amount);

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
        $periodProd = Productions::whereBetween('production_date', [$sDate, $eDate])
            ->where('status', 'completed')->count();
        $periodProdQty = Productions::whereBetween('production_date', [$sDate, $eDate])
            ->where('status', 'completed')->sum('qty_produced');
        $prevProd = Productions::whereBetween('production_date', [$pSDate, $pEDate])
            ->where('status', 'completed')->count();

        // === TOP PRODUCTS ===
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'paid')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select('products.name', DB::raw('SUM(sale_items.qty) as total_qty'), DB::raw('SUM(sale_items.subtotal) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')->limit(5)->get();

        // === SALES TREND CHART ===
        $salesTrend = $this->buildSalesTrendData();

        // === PAYMENT BREAKDOWN ===
        $paymentBreakdown = Sales::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->get()
            ->groupBy('payment_method')
            ->map(function ($items) {
                return (object)[
                    'count' => $items->count(),
                    'total' => $items->sum(fn($s) => $s->total_amount - $s->tax_amount)
                ];
            });

        return view('livewire.admin.dashboard.index', [
            'dashboardRole' => $this->dashboardRole,
            'period' => $this->period,
            'chartDays' => $this->chartDays,
            'salesChartScope' => $this->salesChartScope,
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
            'topProducts' => $topProducts,
            'salesTrendLabels' => $salesTrend['labels'],
            'salesTrendSeries' => $salesTrend['series'],
            'paymentBreakdown' => $paymentBreakdown,
        ]);
    }
}
