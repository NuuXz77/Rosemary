<?php

namespace App\Livewire\Admin\Dashboard\Partials;

use App\Models\Sales;
use Carbon\Carbon;
use Livewire\Component;

class Cashier extends Component
{
    public string $period = 'month';
    public string $chartScope = 'daily';

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

    private function percentChange(float|int $current, float|int $previous): ?float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function updatedChartScope($value): void
    {
        $allowedScopes = ['daily', 'weekly', 'monthly', 'yearly'];
        $this->chartScope = in_array($value, $allowedScopes, true) ? $value : 'daily';
    }

    private function buildDailySalesTrend(): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $raw = Sales::selectRaw('DATE(created_at) as bucket, SUM(total_amount) as total')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $labels = [];
        $series = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $series[] = (float) ($raw[$key] ?? 0);
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
        return match ($this->chartScope) {
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

        $periodSales = (float) Sales::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->sum('total_amount');

        $prevSales = (float) Sales::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'paid')
            ->sum('total_amount');

        $periodTx = Sales::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->count();

        $prevTx = Sales::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'paid')
            ->count();

        $avgTicket = $periodTx > 0 ? ($periodSales / $periodTx) : 0;
        $prevAvgTicket = $prevTx > 0 ? ($prevSales / $prevTx) : 0;

        $salesTrend = $this->buildSalesTrendData();

        return view('livewire.admin.dashboard.partials.cashier', [
            'period' => $this->period,
            'chartScope' => $this->chartScope,
            'periodSales' => $periodSales,
            'salesChange' => $this->percentChange($periodSales, $prevSales),
            'periodTx' => $periodTx,
            'txChange' => $this->percentChange($periodTx, $prevTx),
            'avgTicket' => $avgTicket,
            'avgTicketChange' => $this->percentChange($avgTicket, $prevAvgTicket),
            'salesTrendLabels' => $salesTrend['labels'],
            'salesTrendSeries' => $salesTrend['series'],
            'recentSales' => Sales::with(['customer'])
                ->where('status', 'paid')
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }
}
