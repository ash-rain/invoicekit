<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\Clients\ClientList;
use App\Livewire\Timer\ActiveTimer;
use App\Livewire\Invoices\InvoiceList;

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', Dashboard::class)->name('dashboard');

    // Clients
    Route::get('/clients', ClientList::class)->name('clients.index');
    Route::get('/clients/create', fn () => view('clients.create'))->name('clients.create');
    Route::get('/clients/{client}/edit', fn ($client) => view('clients.edit', compact('client')))->name('clients.edit');

    // Projects
    Route::get('/projects', fn () => view('projects.index'))->name('projects.index');
    Route::get('/projects/create', fn () => view('projects.create'))->name('projects.create');
    Route::get('/projects/{project}/edit', fn ($project) => view('projects.edit', compact('project')))->name('projects.edit');

    // Timer
    Route::get('/timer', ActiveTimer::class)->name('timer');

    // Invoices
    Route::get('/invoices', InvoiceList::class)->name('invoices.index');
    Route::get('/invoices/create', fn () => view('invoices.create'))->name('invoices.create');
    Route::get('/invoices/{invoice}', fn ($invoice) => view('invoices.show', compact('invoice')))->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', fn ($invoice) => view('invoices.edit', compact('invoice')))->name('invoices.edit');

    // Invoice PDF
    Route::get('/invoices/{invoice}/pdf', function ($invoice) {
        $invoice = \App\Models\Invoice::with(['client', 'items', 'user'])->findOrFail($invoice);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    })->name('invoices.pdf');
});

// Public invoice view (for payment links)
Route::get('/pay/{invoice}', function ($invoice) {
    $invoice = \App\Models\Invoice::with(['client', 'items'])->findOrFail($invoice);
    return view('invoices.pay', compact('invoice'));
})->name('invoices.pay');
