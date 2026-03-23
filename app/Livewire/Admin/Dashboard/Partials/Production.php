<?php

namespace App\Livewire\Admin\Dashboard\Partials;

use App\Models\Productions;
use App\Models\ProductStocks;
use App\Models\ProductWastes;
use App\Models\StudentGroups;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Production extends Component
{
    public string $period = 'month';
    public string $chartScope = 'daily';
    #[Reactive]
    public ?string $parentPeriod = null;
    public ?int $productionGroupId = null;
    public ?string $productionGroupName = null;
    public bool $embedded = false;
    public bool $overallProductionScope = false;

    public function mount(?int $productionGroupId = null, ?string $productionGroupName = null, bool $embedded = false, ?string $parentPeriod = null): void
    {
        $this->embedded = $embedded;
        $this->parentPeriod = $parentPeriod;
        $this->productionGroupId = $productionGroupId;
        $this->productionGroupName = $productionGroupName;

        if ($this->parentPeriod && in_array($this->parentPeriod, ['today', 'week', 'month', 'year'], true)) {
            $this->period = $this->parentPeriod;
        }

        if ($this->productionGroupId) {
            return;
        }

        $user = auth()->user();
        if (!$user) {
            return;
        }

        if ($user->hasAnyRole(['Admin', 'admin'])) {
            $this->overallProductionScope = true;
            $this->productionGroupName = 'Semua Tim';
            return;
        }

        if (!$user->hasRole('Production')) {
            return;
        }

        $group = StudentGroups::query()
            ->where('group_code', $user->username)
            ->where('status', true)
            ->first();

        $this->productionGroupId = $group?->id;
        $this->productionGroupName = $group?->name;
    }

    public function updatedChartScope($value): void
    {
        $allowedScopes = ['daily', 'weekly', 'monthly', 'yearly'];
        $this->chartScope = in_array($value, $allowedScopes, true) ? $value : 'daily';
    }

    public function updatedParentPeriod($value): void
    {
        if (in_array($value, ['today', 'week', 'month', 'year'], true)) {
            $this->period = $value;
        }
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

    private function buildTrendData(?int $groupId = null): array
    {
        return match ($this->chartScope) {
            'weekly' => $this->buildWeeklyTrend($groupId),
            'monthly' => $this->buildMonthlyTrend($groupId),
            'yearly' => $this->buildYearlyTrend($groupId),
            default => $this->buildDailyTrend($groupId),
        };
    }

    private function buildDailyTrend(?int $groupId = null): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $raw = Productions::selectRaw('DATE(production_date) as bucket, COUNT(*) as total')
            ->when($groupId, function ($query) use ($groupId) {
                $query->where('student_group_id', $groupId);
            })
            ->where('status', 'completed')
            ->whereBetween('production_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $labels = [];
        $series = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $series[] = (int) ($raw[$key] ?? 0);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function buildWeeklyTrend(?int $groupId = null): array
    {
        $start = now()->subMonth()->startOfDay();
        $end = now()->endOfDay();

        $dailyRows = Productions::selectRaw('DATE(production_date) as bucket, COUNT(*) as total')
            ->when($groupId, function ($query) use ($groupId) {
                $query->where('student_group_id', $groupId);
            })
            ->where('status', 'completed')
            ->whereBetween('production_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('bucket')
            ->get();

        $bucketed = [];
        foreach ($dailyRows as $row) {
            $weekStart = Carbon::parse($row->bucket)->startOfWeek()->toDateString();
            $bucketed[$weekStart] = ($bucketed[$weekStart] ?? 0) + (int) $row->total;
        }

        $labels = [];
        $series = [];
        $cursor = $start->copy()->startOfWeek();

        while ($cursor->lte($end)) {
            $weekStart = $cursor->copy();
            $weekEnd = $cursor->copy()->endOfWeek();
            $key = $weekStart->toDateString();

            $labels[] = $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m');
            $series[] = (int) ($bucketed[$key] ?? 0);

            $cursor->addWeek();
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function buildMonthlyTrend(?int $groupId = null): array
    {
        $start = now()->startOfMonth()->subMonths(11);
        $end = now()->endOfMonth();

        $raw = Productions::selectRaw("DATE_FORMAT(production_date, '%Y-%m') as bucket, COUNT(*) as total")
            ->when($groupId, function ($query) use ($groupId) {
                $query->where('student_group_id', $groupId);
            })
            ->where('status', 'completed')
            ->whereBetween('production_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $labels = [];
        $series = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('M y');
            $series[] = (int) ($raw[$key] ?? 0);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function buildYearlyTrend(?int $groupId = null): array
    {
        $currentYear = now()->year;
        $startYear = $currentYear - 4;

        $raw = Productions::selectRaw('YEAR(production_date) as bucket, COUNT(*) as total')
            ->when($groupId, function ($query) use ($groupId) {
                $query->where('student_group_id', $groupId);
            })
            ->where('status', 'completed')
            ->whereYear('production_date', '>=', $startYear)
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $labels = [];
        $series = [];

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $labels[] = (string) $year;
            $series[] = (int) ($raw[$year] ?? 0);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function buildDashboardData($start, $end, $prevStart, $prevEnd): array
    {
        if (!$this->productionGroupId && !$this->overallProductionScope) {
            return [
                'periodProd' => 0,
                'periodProdQty' => 0,
                'prodChange' => null,
                'draftProd' => 0,
                'productWasteQty' => 0,
                'lowStockProducts' => 0,
                'productionTrendLabels' => [],
                'productionTrendSeries' => [],
            ];
        }

        $groupId = $this->overallProductionScope ? null : $this->productionGroupId;
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();
        $prevStartDate = $prevStart->toDateString();
        $prevEndDate = $prevEnd->toDateString();

        $groupProductIds = Productions::query()
            ->when($groupId, function ($query) use ($groupId) {
                $query->where('student_group_id', $groupId);
            })
            ->distinct()
            ->pluck('product_id');

        $periodProd = Productions::whereBetween('production_date', [$startDate, $endDate])
            ->when($groupId, function ($query) use ($groupId) {
                $query->where('student_group_id', $groupId);
            })
            ->where('status', 'completed')
            ->count();

        $periodProdQty = Productions::whereBetween('production_date', [$startDate, $endDate])
            ->when($groupId, function ($query) use ($groupId) {
                $query->where('student_group_id', $groupId);
            })
            ->where('status', 'completed')
            ->sum(DB::raw('COALESCE(actual_qty, qty_produced)'));

        $prevProd = Productions::whereBetween('production_date', [$prevStartDate, $prevEndDate])
            ->when($groupId, function ($query) use ($groupId) {
                $query->where('student_group_id', $groupId);
            })
            ->where('status', 'completed')
            ->count();

        $draftProd = Productions::query()
            ->when($groupId, function ($query) use ($groupId) {
                $query->where('student_group_id', $groupId);
            })
            ->where('status', 'draft')
            ->count();

        $productWasteQuery = ProductWastes::query()
            ->whereBetween('waste_date', [$startDate, $endDate])
            ->whereHas('production', function ($query) use ($groupId) {
                if ($groupId) {
                    $query->where('student_group_id', $groupId);
                }
            });

        $productWasteQty = $productWasteQuery->sum('qty');

        $lowStockProducts = ProductStocks::query()
            ->when($groupId, function ($query) use ($groupProductIds) {
                $query->whereIn('product_id', $groupProductIds);
            })
            ->where('qty_available', '<=', 5)
            ->count();

        $trend = $this->buildTrendData($groupId);

        return [
            'periodProd' => $periodProd,
            'periodProdQty' => $periodProdQty,
            'prodChange' => $this->percentChange($periodProd, $prevProd),
            'draftProd' => $draftProd,
            'productWasteQty' => $productWasteQty,
            'lowStockProducts' => $lowStockProducts,
            'productionTrendLabels' => $trend['labels'],
            'productionTrendSeries' => $trend['series'],
        ];
    }

    public function render()
    {
        [$start, $end] = $this->getPeriodRange();
        [$prevStart, $prevEnd] = $this->getPreviousPeriodRange();

        return view('livewire.admin.dashboard.partials.production', array_merge([
            'period' => $this->period,
            'chartScope' => $this->chartScope,
            'productionGroupId' => $this->productionGroupId,
            'productionGroupName' => $this->productionGroupName,
            'embedded' => $this->embedded,
            'overallProductionScope' => $this->overallProductionScope,
        ], $this->buildDashboardData($start, $end, $prevStart, $prevEnd)));
    }
}
