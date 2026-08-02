<?php

namespace Tests\Feature;

use Tests\TestCase;

class MinioInitCommandTest extends TestCase
{
    public function test_it_skips_when_no_s3_disk_is_configured(): void
    {
        config(['filesystems.disks.minio' => ['driver' => 'local', 'root' => storage_path('app')]]);

        $this->artisan('storage:minio-init')
            ->expectsOutputToContain('nothing to do')
            ->assertSuccessful();
    }

    public function test_it_skips_when_minio_disk_is_absent(): void
    {
        config(['filesystems.disks.minio' => null]);

        $this->artisan('storage:minio-init')
            ->expectsOutputToContain('nothing to do')
            ->assertSuccessful();
    }

    public function test_it_fails_when_bucket_is_not_configured(): void
    {
        config(['filesystems.disks.minio' => [
            'driver' => 's3',
            'bucket' => null,
            'endpoint' => 'http://minio:9000',
            'key' => 'x',
            'secret' => 'y',
        ]]);

        $this->artisan('storage:minio-init')
            ->expectsOutputToContain('no bucket configured')
            ->assertFailed();
    }
}
