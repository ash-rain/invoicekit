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
            'due_today' => "Payment Due Today — Invoice {$this->invoice->invoice_number}",
            'overdue' => "Overdue Invoice — {$this->invoice->invoice_number}",
            default => "Payment Reminder — Invoice {$this->invoice->invoice_number}",
        };

        $body = match ($this->reminderType) {
            'due_today' => "Invoice {$this->invoice->invoice_number} for {$this->invoice->client->name} is due today.",
            'overdue' => "Invoice {$this->invoice->invoice_number} for {$this->invoice->client->name} is overdue.",
            default => "Invoice {$this->invoice->invoice_number} for {$this->invoice->client->name} is due soon.",
        };

        return (new MailMessage)
            ->subject($subject)
            ->line($body)
            ->action('View Invoice', route('invoices.show', $this->invoice));
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        $title = match ($this->reminderType) {
            'due_today' => 'Invoice Due Today',
            'overdue' => 'Invoice Overdue',
            default => 'Invoice Reminder',
        };

        $body = match ($this->reminderType) {
            'due_today' => "Invoice {$this->invoice->invoice_number} for {$this->invoice->client->name} is due today.",
            'overdue' => "Invoice {$this->invoice->invoice_number} for {$this->invoice->client->name} is now overdue.",
            default => "Invoice {$this->invoice->invoice_number} for {$this->invoice->client->name} is due soon.",
        };

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->action('View Invoice', route('invoices.show', $this->invoice));
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
                'due_today' => "Invoice {$this->invoice->invoice_number} for {$this->invoice->client->name} is due today.",
                'overdue' => "Invoice {$this->invoice->invoice_number} for {$this->invoice->client->name} is now overdue.",
                default => "Invoice {$this->invoice->invoice_number} for {$this->invoice->client->name} is due soon.",
            },
            'url' => route('invoices.show', $this->invoice),
        ];
    }
}
