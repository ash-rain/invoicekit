<?php

namespace App\Console\Commands;

use App\Jobs\SendInvoiceReminder;
use App\Models\Invoice;
use Illuminate\Console\Command;

class ProcessInvoiceReminders extends Command
{
    protected $signature = 'invoices:process-reminders';

    protected $description = 'Flag overdue invoices and dispatch reminder emails';

    public function handle(): int
    {
        $today = now()->toDateString();

        // ── 1. Auto-flag sent invoices as overdue ────────────────────────
        $flagged = Invoice::where('status', 'sent')
            ->whereDate('due_date', '<', $today)
            ->update(['status' => 'overdue']);

        $this->info("Flagged {$flagged} invoice(s) as overdue.");

        // ── 2. Reminder: 3 days before due date ──────────────────────────
        $dueSoon = Invoice::where('status', 'sent')
            ->whereDate('due_date', now()->addDays(3)->toDateString())
            ->with(['client', 'items', 'user'])
            ->get();

        foreach ($dueSoon as $invoice) {
            SendInvoiceReminder::dispatch($invoice, 'due_soon');
        }
        $this->info("Queued {$dueSoon->count()} due-soon reminder(s).");

        // ── 3. Reminder: due today ────────────────────────────────────────
        $dueToday = Invoice::where('status', 'sent')
            ->whereDate('due_date', $today)
            ->with(['client', 'items', 'user'])
            ->get();

        foreach ($dueToday as $invoice) {
            SendInvoiceReminder::dispatch($invoice, 'due_today');
        }
        $this->info("Queued {$dueToday->count()} due-today reminder(s).");

        // ── 4. Reminder: 7 days overdue ───────────────────────────────────
        $sevenDaysOverdue = Invoice::where('status', 'overdue')
            ->whereDate('due_date', now()->subDays(7)->toDateString())
            ->with(['client', 'items', 'user'])
            ->get();

        foreach ($sevenDaysOverdue as $invoice) {
            SendInvoiceReminder::dispatch($invoice, 'overdue');
        }
        $this->info("Queued {$sevenDaysOverdue->count()} overdue reminder(s).");

        return self::SUCCESS;
    }
}
