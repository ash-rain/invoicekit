<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthComparisonChart extends ChartWidget
{
    protected ?string $heading = 'Month over Month: New Paid Subscriptions';

    protected function getData(): array
    {
        $currentMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $daysInCurrentMonth = now()->daysInMonth;
        $daysInLastMonth = now()->subMonthNoOverflow()->daysInMonth;
        $maxDays = max($daysInCurrentMonth, $daysInLastMonth);

        $buildCounts = function (Carbon $start, int $days): array {
            $counts = User::whereIn('plan', ['starter', 'pro'])
                ->where('subscription_status', 'active')
                ->where('created_at', '>=', $start)
                ->where('created_at', '<', $start->copy()->addMonth())
                ->selectRaw('EXTRACT(DAY FROM created_at)::int as day, COUNT(*) as count')
                ->groupBy('day')
                ->pluck('count', 'day');

            return collect(range(1, $days))
                ->map(fn (int $day) => $counts[$day] ?? 0)
                ->values()
                ->all();
        };

        $currentMonthData = $buildCounts($currentMonthStart->copy(), $daysInCurrentMonth);
        $lastMonthData = $buildCounts($lastMonthStart->copy(), $daysInLastMonth);

        $labels = collect(range(1, $maxDays))->map(fn (int $d) => 'Day '.$d)->all();

        return [
            'datasets' => [
                [
                    'label' => now()->format('F Y'),
                    'data' => $currentMonthData,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'tension' => 0.3,
                    'fill' => false,
                ],
                [
                    'label' => now()->subMonthNoOverflow()->format('F Y'),
                    'data' => $lastMonthData,
                    'borderColor' => 'rgb(99, 102, 241)',
                    'tension' => 0.3,
                    'fill' => false,
                    'borderDash' => [5, 5],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
