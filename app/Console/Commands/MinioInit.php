<?php

namespace App\Console\Commands;

use Aws\S3\S3Client;
use Illuminate\Console\Command;

class MinioInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:minio-init {--tries=10 : Connection attempts while MinIO starts up}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure the configured MinIO bucket exists (idempotent; safe to run on every boot).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $config = config('filesystems.disks.minio');

        if (! $config || ($config['driver'] ?? null) !== 's3') {
            $this->warn('No MinIO (s3) disk configured; nothing to do.');

            return self::SUCCESS;
        }

        $bucket = $config['bucket'] ?? null;

        if (! $bucket) {
            $this->error('MinIO disk has no bucket configured (MINIO_BUCKET).');

            return self::FAILURE;
        }

        $client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'] ?? 'us-east-1',
            'endpoint' => $config['endpoint'],
            'use_path_style_endpoint' => $config['use_path_style_endpoint'] ?? true,
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
        ]);

        $tries = max(1, (int) $this->option('tries'));

        for ($attempt = 1; $attempt <= $tries; $attempt++) {
            try {
                if ($client->doesBucketExist($bucket)) {
                    $this->info("MinIO bucket '{$bucket}' already exists.");

                    return self::SUCCESS;
                }

                $client->createBucket(['Bucket' => $bucket]);
                $this->info("Created MinIO bucket '{$bucket}'.");

                return self::SUCCESS;
            } catch (\Aws\S3\Exception\S3Exception $e) {
                // The bucket exists but is owned by us — treat as success.
                if (in_array($e->getAwsErrorCode(), ['BucketAlreadyOwnedByYou', 'BucketAlreadyExists'], true)) {
                    $this->info("MinIO bucket '{$bucket}' already exists.");

                    return self::SUCCESS;
                }

                $this->warn("Attempt {$attempt}/{$tries} failed: {$e->getAwsErrorCode()}");
            } catch (\Throwable $e) {
                // Connection errors while MinIO is still booting — retry.
                $this->warn("Attempt {$attempt}/{$tries}: MinIO not reachable yet ({$e->getMessage()})");
            }

            if ($attempt < $tries) {
                sleep(2);
            }
        }

        $this->error("Could not ensure MinIO bucket '{$bucket}' after {$tries} attempts.");

        return self::FAILURE;
    }
}
