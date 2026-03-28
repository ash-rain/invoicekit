<?php

use App\Console\Commands\ProcessInvoiceReminders;
use App\Console\Commands\ProcessRecurringInvoices;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run every day at 08:00 — flag overdue invoices and send reminder emails
Schedule::command(ProcessInvoiceReminders::class)->dailyAt('08:00');

// Run every day at 06:00 — generate invoices from active recurring templates
Schedule::command(ProcessRecurringInvoices::class)->dailyAt('06:00');
