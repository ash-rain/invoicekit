<?php

namespace App\Console\Commands;

use App\Models\RecurringInvoice;
use Illuminate\Console\Command;

class ProcessRecurringInvoices extends Command
{
    protected $signature = 'invoices:process-recurring';

    protected $description = 'Generate invoices from active recurring invoice templates that are due today.';

    public function handle(): int
    {
        $due = RecurringInvoice::with(['items', 'user', 'client'])
            ->where('active', true)
            ->whereDate('next_send_date', '<=', now()->toDateString())
            ->get();

        $count = 0;

        foreach ($due as $recurring) {
            $invoice = $recurring->generateInvoice();
            $count++;
            $this->line("Generated invoice {$invoice->invoice_number} for recurring #{$recurring->id}");
        }

        $this->info("Processed {$count} recurring invoice(s).");

        return Command::SUCCESS;
    }
}
