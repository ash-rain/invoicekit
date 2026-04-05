<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Services\PlanService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubscriptionStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $activeCount = User::where('subscription_status', 'active')->count();
        $starterCount = User::where('plan', 'starter')->where('subscription_status', 'active')->count();
        $proCount = User::where('plan', 'pro')->where('subscription_status', 'active')->count();
        $trialingCount = User::where('subscription_status', 'trialing')->count();
        $freeCount = User::where('plan', 'free')->count();

        $starterPrice = PlanService::PLANS['starter']['price'];
        $proPrice = PlanService::PLANS['pro']['price'];
        $mrr = ($starterCount * $starterPrice) + ($proCount * $proPrice);

        return [
            Stat::make('Active Subscriptions', $activeCount)
                ->description('Paid subscribers')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Pro Plan', $proCount)
                ->description('$'.$proPrice.'/mo each')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            Stat::make('Starter Plan', $starterCount)
                ->description('$'.$starterPrice.'/mo each')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('info'),
            Stat::make('Trialing', $trialingCount)
                ->description('On trial')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray'),
            Stat::make('Free Plan', $freeCount)
                ->description('No subscription')
                ->descriptionIcon('heroicon-m-user')
                ->color('gray'),
            Stat::make('MRR', '$'.number_format($mrr))
                ->description('Monthly recurring revenue')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
