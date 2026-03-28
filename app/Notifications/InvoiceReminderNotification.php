<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class InvoiceReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $reminderType  'due_soon' | 'due_today' | 'overdue'
     */
    public function __construct(
        public readonly Invoice $invoice,
        public readonly string $reminderType = 'due_soon',
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail', WebPushChannel::class, 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->reminderType) {
            'due_today' => __('Payment Due Today — Invoice :number', ['number' => $this->invoice->invoice_number]),
            'overdue' => __('Overdue Invoice — :number', ['number' => $this->invoice->invoice_number]),
            default => __('Payment Reminder — Invoice :number', ['number' => $this->invoice->invoice_number]),
        };

        $body = match ($this->reminderType) {
            'due_today' => __('Invoice :number for :client is due today.', ['number' => $this->invoice->invoice_number, 'client' => $this->invoice->client->name]),
            'overdue' => __('Invoice :number for :client is overdue.', ['number' => $this->invoice->invoice_number, 'client' => $this->invoice->client->name]),
            default => __('Invoice :number for :client is due soon.', ['number' => $this->invoice->invoice_number, 'client' => $this->invoice->client->name]),
        };

        return (new MailMessage)
            ->subject($subject)
            ->line($body)
            ->action(__('View Invoice'), route('invoices.show', $this->invoice));
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        $title = match ($this->reminderType) {
            'due_today' => __('Invoice Due Today'),
            'overdue' => __('Invoice Overdue'),
            default => __('Invoice Reminder'),
        };

        $body = match ($this->reminderType) {
            'due_today' => __('Invoice :number for :client is due today.', ['number' => $this->invoice->invoice_number, 'client' => $this->invoice->client->name]),
            'overdue' => __('Invoice :number for :client is now overdue.', ['number' => $this->invoice->invoice_number, 'client' => $this->invoice->client->name]),
            default => __('Invoice :number for :client is due soon.', ['number' => $this->invoice->invoice_number, 'client' => $this->invoice->client->name]),
        };

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->action(__('View Invoice'), route('invoices.show', $this->invoice));
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'invoice_reminder',
            'reminder_type' => $this->reminderType,
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'client_name' => $this->invoice->client->name,
            'message' => match ($this->reminderType) {
                'due_today' => __('Invoice :number for :client is due today.', ['number' => $this->invoice->invoice_number, 'client' => $this->invoice->client->name]),
                'overdue' => __('Invoice :number for :client is now overdue.', ['number' => $this->invoice->invoice_number, 'client' => $this->invoice->client->name]),
                default => __('Invoice :number for :client is due soon.', ['number' => $this->invoice->invoice_number, 'client' => $this->invoice->client->name]),
            },
            'url' => route('invoices.show', $this->invoice),
        ];
    }
}
