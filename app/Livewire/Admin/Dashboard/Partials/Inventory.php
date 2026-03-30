<?php

namespace App\Livewire\Admin\Dashboard\Partials;

use App\Models\MaterialStockLogs;
use App\Models\MaterialStocks;
use App\Models\MaterialWastes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Inventory extends Component
{
    public string $period = 'month';
    public string $chartScope = 'daily';
    #[Reactive]
    public ?string $parentPeriod = null;
    public bool $embedded = false;

    public function mount(bool $embedded = false, ?string $parentPeriod = null): void
    {
        $this->embedded = $embedded;
        $this->parentPeriod = $parentPeriod;

        if ($this->parentPeriod && in_array($this->parentPeriod, ['today', 'week', 'month', 'year'], true)) {
            $this->period = $this->parentPeriod;
        }
    }

    public function updatedParentPeriod($value): void
    {
        if (in_array($value, ['today', 'week', 'month', 'year'], true)) {
            $this->period = $value;
        }
    }

    public function updatedChartDays($value): void
    {
        $this->chartScope = 'daily';
    }

    public function updatedChartScope($value): void
    {
        $allowedScopes = ['daily', 'weekly', 'monthly', 'yearly'];
        $this->chartScope = in_array($value, $allowedScopes, true) ? $value : 'daily';
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
        if ($previous == 0) {
            return $current > 0 ? 100 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function buildDailyInventoryTrend(): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $rows = MaterialStockLogs::selectRaw("DATE(created_at) as bucket, SUM(CASE WHEN type = 'in' THEN ABS(qty) ELSE 0 END) as total_in, SUM(CASE WHEN type = 'out' THEN ABS(qty) ELSE 0 END) as total_out")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('bucket')
            ->get();

        $rawIn = $rows->pluck('total_in', 'bucket');
        $rawOut = $rows->pluck('total_out', 'bucket');

        $labels = [];
        $seriesIn = [];
        $seriesOut = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $seriesIn[] = (float) ($rawIn[$key] ?? 0);
            $seriesOut[] = (float) ($rawOut[$key] ?? 0);
        }

        return ['labels' => $labels, 'seriesIn' => $seriesIn, 'seriesOut' => $seriesOut];
    }

    private function buildWeeklyInventoryTrend(): array
    {
        $start = now()->subMonth()->startOfDay();
        $end = now()->endOfDay();

        $rows = MaterialStockLogs::selectRaw("DATE(created_at) as bucket, SUM(CASE WHEN type = 'in' THEN ABS(qty) ELSE 0 END) as total_in, SUM(CASE WHEN type = 'out' THEN ABS(qty) ELSE 0 END) as total_out")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('bucket')
            ->get();

        $bucketedIn = [];
        $bucketedOut = [];

        foreach ($rows as $row) {
            $weekStart = Carbon::parse($row->bucket)->startOfWeek()->toDateString();
            $bucketedIn[$weekStart] = ($bucketedIn[$weekStart] ?? 0) + (float) $row->total_in;
            $bucketedOut[$weekStart] = ($bucketedOut[$weekStart] ?? 0) + (float) $row->total_out;
        }

        $labels = [];
        $seriesIn = [];
        $seriesOut = [];
        $cursor = $start->copy()->startOfWeek();

        while ($cursor->lte($end)) {
            $weekStart = $cursor->copy();
            $weekEnd = $cursor->copy()->endOfWeek();
            $key = $weekStart->toDateString();

            $labels[] = $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m');
            $seriesIn[] = (float) ($bucketedIn[$key] ?? 0);
            $seriesOut[] = (float) ($bucketedOut[$key] ?? 0);

            $cursor->addWeek();
        }

        return ['labels' => $labels, 'seriesIn' => $seriesIn, 'seriesOut' => $seriesOut];
    }

    private function buildMonthlyInventoryTrend(): array
    {
        $start = now()->startOfMonth()->subMonths(11);
        $end = now()->endOfMonth();

        $rows = MaterialStockLogs::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bucket, SUM(CASE WHEN type = 'in' THEN ABS(qty) ELSE 0 END) as total_in, SUM(CASE WHEN type = 'out' THEN ABS(qty) ELSE 0 END) as total_out")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('bucket')
            ->get();

        $rawIn = $rows->pluck('total_in', 'bucket');
        $rawOut = $rows->pluck('total_out', 'bucket');

        $labels = [];
        $seriesIn = [];
        $seriesOut = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('M y');
            $seriesIn[] = (float) ($rawIn[$key] ?? 0);
            $seriesOut[] = (float) ($rawOut[$key] ?? 0);
        }

        return ['labels' => $labels, 'seriesIn' => $seriesIn, 'seriesOut' => $seriesOut];
    }

    private function buildYearlyInventoryTrend(): array
    {
        $currentYear = now()->year;
        $startYear = $currentYear - 4;

        $rows = MaterialStockLogs::selectRaw("YEAR(created_at) as bucket, SUM(CASE WHEN type = 'in' THEN ABS(qty) ELSE 0 END) as total_in, SUM(CASE WHEN type = 'out' THEN ABS(qty) ELSE 0 END) as total_out")
            ->whereYear('created_at', '>=', $startYear)
            ->groupBy('bucket')
            ->get();

        $rawIn = $rows->pluck('total_in', 'bucket');
        $rawOut = $rows->pluck('total_out', 'bucket');

        $labels = [];
        $seriesIn = [];
        $seriesOut = [];

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $labels[] = (string) $year;
            $seriesIn[] = (float) ($rawIn[$year] ?? 0);
            $seriesOut[] = (float) ($rawOut[$year] ?? 0);
        }

        return ['labels' => $labels, 'seriesIn' => $seriesIn, 'seriesOut' => $seriesOut];
    }

    private function buildInventoryTrendData(): array
    {
        return match ($this->chartScope) {
            'weekly' => $this->buildWeeklyInventoryTrend(),
            'monthly' => $this->buildMonthlyInventoryTrend(),
            'yearly' => $this->buildYearlyInventoryTrend(),
            default => $this->buildDailyInventoryTrend(),
        };
    }

    private function buildDashboardData($start, $end, $prevStart, $prevEnd): array
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

        $inventoryTrend = $this->buildInventoryTrendData();

        return [
            'lowStockMaterials' => $lowStockMaterials,
            'totalMaterialItems' => $totalMaterialItems,
            'totalMaterialQty' => $totalMaterialQty,
            'incomingQty' => $incomingQty,
            'outgoingQty' => $outgoingQty,
            'incomingChange' => $this->percentChange($incomingQty, $prevIncomingQty),
            'outgoingChange' => $this->percentChange($outgoingQty, $prevOutgoingQty),
            'materialWasteQty' => $materialWasteQty,
            'inventoryTrendLabels' => $inventoryTrend['labels'],
            'inventoryTrendSeriesIn' => $inventoryTrend['seriesIn'],
            'inventoryTrendSeriesOut' => $inventoryTrend['seriesOut'],
            'chartScope' => $this->chartScope,
        ];
    }

    public function render()
    {
        [$start, $end] = $this->getPeriodRange();
        [$prevStart, $prevEnd] = $this->getPreviousPeriodRange();

        return view('livewire.admin.dashboard.partials.inventory', array_merge([
            'period' => $this->period,
            'chartScope' => $this->chartScope,
            'embedded' => $this->embedded,
        ], $this->buildDashboardData($start, $end, $prevStart, $prevEnd)));
    }
}
