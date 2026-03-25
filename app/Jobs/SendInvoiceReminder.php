<?php

namespace App\Jobs;

use App\Mail\InvoiceReminder;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

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

        // Only send if the client has an email address
        if (empty($this->invoice->client->email)) {
            return;
        }

        Mail::send(new InvoiceReminder($this->invoice, $this->reminderType));
    }
}
