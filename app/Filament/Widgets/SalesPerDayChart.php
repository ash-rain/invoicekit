<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesPerDayChart extends ChartWidget
{
    protected ?string $heading = 'New Paid Subscriptions (Last 30 Days)';

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn (int $daysAgo) => now()->subDays($daysAgo)->startOfDay());

        $counts = User::whereIn('plan', ['starter', 'pro'])
            ->where('subscription_status', 'active')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        return [
            'datasets' => [
                [
                    'label' => 'New Paid Users',
                    'data' => $days->map(fn (Carbon $day) => $counts[$day->toDateString()] ?? 0)->values()->all(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.6)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
            ],
            'labels' => $days->map(fn (Carbon $day) => $day->format('M d'))->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
