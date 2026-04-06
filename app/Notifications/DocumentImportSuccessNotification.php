<?php

namespace App\Notifications;

use App\Models\DocumentImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class DocumentImportSuccessNotification extends Notification implements ShouldQueue
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

    private function reviewUrl(): string
    {
        $routeName = $this->import->document_type === 'invoice'
            ? 'invoices.import.review'
            : 'expenses.import.review';

        return route($routeName, $this->import);
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        $typeLabel = $this->import->document_type === 'invoice' ? __('invoice') : __('expense');

        return (new WebPushMessage)
            ->title(__('Document imported successfully'))
            ->body(__(':filename — Review and confirm to create your :type.', [
                'filename' => $this->import->original_filename,
                'type' => $typeLabel,
            ]))
            ->action(__('Review'), $this->reviewUrl());
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $typeLabel = $this->import->document_type === 'invoice' ? __('invoice') : __('expense');

        return [
            'type' => 'document_import_success',
            'import_id' => $this->import->id,
            'filename' => $this->import->original_filename,
            'document_type' => $this->import->document_type,
            'message' => __(':filename — Review and confirm to create your :type.', [
                'filename' => $this->import->original_filename,
                'type' => $typeLabel,
            ]),
            'url' => $this->reviewUrl(),
        ];
    }
}
