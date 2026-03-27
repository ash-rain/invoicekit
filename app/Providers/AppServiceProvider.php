<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\WebPush\Events\NotificationFailed;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(NotificationFailed::class, function (NotificationFailed $event): void {
            Log::warning('WebPush delivery failed', [
                'endpoint' => substr($event->report->getEndpoint(), 0, 60),
                'expired' => $event->report->isSubscriptionExpired(),
                'reason' => $event->report->getReason(),
            ]);
        });
    }
}
