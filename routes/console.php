<?php

use App\Console\Commands\ProcessInvoiceReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run every day at 08:00 — flag overdue invoices and send reminder emails
Schedule::command(ProcessInvoiceReminders::class)->dailyAt('08:00');
