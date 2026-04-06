<?php

namespace App\Notifications;

use App\Models\DocumentImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class DocumentImportFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly DocumentImport $import,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class, 'database'];
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title(__('Import failed'))
            ->body(__('Failed to process :filename. :error', [
                'filename' => $this->import->original_filename,
                'error' => $this->import->error_message ?? __('Unknown error'),
            ]));
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'document_import_failed',
            'import_id' => $this->import->id,
            'filename' => $this->import->original_filename,
            'document_type' => $this->import->document_type,
            'message' => __('Failed to process :filename. :error', [
                'filename' => $this->import->original_filename,
                'error' => $this->import->error_message ?? __('Unknown error'),
            ]),
            'url' => null,
        ];
    }
}
