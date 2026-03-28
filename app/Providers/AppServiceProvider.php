<?php

namespace App\Providers;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        $this->configureMinioTemporaryUrls();
    }

    /**
     * Configure MinIO temporary URLs to use the public-facing endpoint
     * so that pre-signed URLs generated server-side resolve in the browser.
     */
    private function configureMinioTemporaryUrls(): void
    {
        $publicEndpoint = env('MINIO_PUBLIC_ENDPOINT');

        if (! $publicEndpoint) {
            return;
        }

        Storage::disk('minio')->buildTemporaryUrlsUsing(function (string $path, \DateTimeInterface $expiration, array $options) use ($publicEndpoint): string {
            $config = config('filesystems.disks.minio');

            $client = new S3Client([
                'version' => 'latest',
                'region' => $config['region'],
                'endpoint' => $publicEndpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
            ]);

            $command = $client->getCommand('PutObject', array_merge([
                'Bucket' => $config['bucket'],
                'Key' => $path,
                'ACL' => 'private',
            ], $options));

            return (string) $client->createPresignedRequest($command, $expiration)->getUri();
        });
    }
}
