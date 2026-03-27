<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Notifications\InvoiceReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInvoiceReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    /**
     * @param  string  $reminderType  'due_soon' | 'due_today' | 'overdue'
     */
    public function __construct(
        public readonly Invoice $invoice,
        public readonly string $reminderType = 'due_soon',
    ) {}

    public function handle(): void
    {
        // Only send if the invoice still needs attention
        if (! in_array($this->invoice->status, ['sent', 'overdue'])) {
            return;
        }

        // Only send if the invoice owner exists
        if (! $this->invoice->user) {
            return;
        }

        $this->invoice->user->notify(new InvoiceReminderNotification($this->invoice, $this->reminderType));
    }
}
