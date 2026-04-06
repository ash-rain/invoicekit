<?php

namespace App\Jobs;

use App\Exceptions\NoAvailableApiKeyException;
use App\Models\DocumentImport;
use App\Notifications\DocumentImportFailedNotification;
use App\Notifications\DocumentImportSuccessNotification;
use App\Services\GeminiExtractionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDocumentImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly DocumentImport $import,
    ) {}

    public function handle(GeminiExtractionService $gemini): void
    {
        $this->import->update(['status' => 'processing']);

        try {
            $extractedData = $gemini->extractFromDocument(
                $this->import->stored_path,
                $this->import->mime_type,
                $this->import->document_type,
            );

            $this->import->update([
                'status' => 'extracted',
                'extracted_data' => $extractedData,
                'error_message' => null,
            ]);

            $this->import->user->notify(new DocumentImportSuccessNotification($this->import));
        } catch (NoAvailableApiKeyException $e) {
            $this->fail($e);
        } catch (\Throwable $e) {
            $this->import->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 1000),
            ]);

            $this->import->user->notify(new DocumentImportFailedNotification($this->import));
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->import->update([
            'status' => 'failed',
            'error_message' => substr($exception->getMessage(), 0, 1000),
        ]);

        $this->import->user->notify(new DocumentImportFailedNotification($this->import));
    }
}
