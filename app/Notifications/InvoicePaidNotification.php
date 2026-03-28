<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class InvoicePaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Invoice $invoice,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class, 'database'];
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title(__('Invoice Paid'))
            ->body(__('Invoice :number for :client has been marked as paid.', [
                'number' => $this->invoice->invoice_number,
                'client' => $this->invoice->client->name,
            ]))
            ->action(__('View Invoice'), route('invoices.show', $this->invoice));
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'invoice_paid',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'client_name' => $this->invoice->client->name,
            'message' => __('Invoice :number for :client has been marked as paid.', [
                'number' => $this->invoice->invoice_number,
                'client' => $this->invoice->client->name,
            ]),
            'url' => route('invoices.show', $this->invoice),
        ];
    }
}
