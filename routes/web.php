<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\InvoicePortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\StripeWebhookController;
use App\Livewire\Clients\ClientList;
use App\Livewire\Clients\CreateEditClient;
use App\Livewire\Dashboard;
use App\Livewire\Expenses\CreateExpense;
use App\Livewire\Expenses\ExpenseList;
use App\Livewire\Invoices\CreateInvoice;
use App\Livewire\Invoices\CreateRecurringInvoice;
use App\Livewire\Invoices\InvoiceList;
use App\Livewire\Invoices\RecurringInvoiceList;
use App\Livewire\OnboardingWizard;
use App\Livewire\Projects\CreateEditProject;
use App\Livewire\Projects\ProjectDetail;
use App\Livewire\Projects\ProjectList;
use App\Livewire\Settings;
use Illuminate\Support\Facades\Route;

// ── Landing page ─────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});

// ── Legal pages ───────────────────────────────────────────────────────────────
Route::get('/privacy', fn () => view('legal.privacy'))->name('privacy');
Route::get('/terms', fn () => view('legal.terms'))->name('terms');

// ── SEO ───────────────────────────────────────────────────────────────────────
Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => url('/'), 'priority' => '1.0'],
        ['loc' => url('/register'), 'priority' => '0.9'],
        ['loc' => url('/login'), 'priority' => '0.8'],
        ['loc' => url('/privacy'), 'priority' => '0.4'],
        ['loc' => url('/terms'), 'priority' => '0.4'],
    ];

    return response()->view('sitemap', compact('urls'))
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Disallow: /dashboard\n";
    $content .= "Disallow: /settings\n";
    $content .= "Disallow: /clients\n";
    $content .= "Disallow: /projects\n";
    $content .= "Disallow: /invoices\n";
    $content .= "Disallow: /timer\n";
    $content .= "Disallow: /billing\n";
    $content .= "Disallow: /onboarding\n";
    $content .= 'Sitemap: '.route('sitemap')."\n";

    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

// ── Locale switcher ──────────────────────────────────────────────────────────
Route::post('/locale', function (\Illuminate\Http\Request $request) {
    $locale = $request->input('locale');

    if (in_array($locale, config('invoicekit.supported_languages', ['en']))) {
        session(['locale' => $locale]);
    }

    return back();
})->name('locale.switch');

// ── Onboarding (auth required, email NOT required here) ───────────────────────
Route::middleware('auth')->get('/onboarding', OnboardingWizard::class)->name('onboarding');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Profile (legacy redirect) + Settings
    Route::get('/profile', fn () => redirect()->route('settings.index'))->name('profile.edit');
    Route::get('/settings', Settings::class)->name('settings.index');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Clients
    Route::get('/clients', ClientList::class)->name('clients.index');
    Route::get('/clients/create', CreateEditClient::class)->name('clients.create');
    Route::get('/clients/{client}/edit', CreateEditClient::class)->name('clients.edit');

    // Projects
    Route::get('/projects', ProjectList::class)->name('projects.index');
    Route::get('/projects/create', CreateEditProject::class)->name('projects.create');
    Route::get('/projects/{project}', ProjectDetail::class)->name('projects.show');
    Route::get('/projects/{project}/edit', CreateEditProject::class)->name('projects.edit');

    // Timer
    Route::get('/timer', fn () => view('timer.index'))->name('timer');

    // Invoices
    Route::get('/invoices', InvoiceList::class)->name('invoices.index');
    Route::get('/invoices/create', CreateInvoice::class)->name('invoices.create');
    Route::get('/invoices/{invoice}', function ($invoice) {
        $inv = \App\Models\Invoice::findOrFail($invoice);
        if ($inv->user_id !== auth()->id()) {
            abort(403);
        }

        return view('invoices.show', compact('invoice'));
    })->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', CreateInvoice::class)->name('invoices.edit');

    // Recurring Invoices (Pro)
    Route::get('/recurring-invoices', RecurringInvoiceList::class)->name('recurring-invoices.index');
    Route::get('/recurring-invoices/create', CreateRecurringInvoice::class)->name('recurring-invoices.create');
    Route::get('/recurring-invoices/{recurringInvoice}/edit', CreateRecurringInvoice::class)->name('recurring-invoices.edit');

    // Expenses
    Route::get('/expenses', ExpenseList::class)->name('expenses.index');
    Route::get('/expenses/create', CreateExpense::class)->name('expenses.create');
    Route::get('/expenses/{expense}/edit', CreateExpense::class)->name('expenses.edit');
    Route::get('/expenses/export', function () {
        $expenses = \App\Models\Expense::where('user_id', auth()->id())
            ->with(['client', 'project'])
            ->orderByDesc('date')
            ->get();

        $filename = 'expenses-'.now()->format('Y-m').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Description', 'Category', 'Client', 'Project', 'Amount', 'Currency', 'Billable']);
            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->date->format('Y-m-d'),
                    $expense->description,
                    $expense->category,
                    $expense->client?->name ?? '',
                    $expense->project?->name ?? '',
                    $expense->amount,
                    $expense->currency,
                    $expense->billable ? 'Yes' : 'No',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    })->name('expenses.export');

    // Make invoice recurring (Pro)
    Route::post('/invoices/{invoice}/make-recurring', function ($invoiceId) {
        $inv = \App\Models\Invoice::with('items')->findOrFail($invoiceId);
        if ($inv->user_id !== auth()->id()) {
            abort(403);
        }
        if (! auth()->user()->isPro()) {
            return back()->withErrors(['plan' => 'Recurring invoices are a Pro feature.']);
        }

        $recurring = \App\Models\RecurringInvoice::create([
            'user_id' => $inv->user_id,
            'client_id' => $inv->client_id,
            'frequency' => 'monthly',
            'next_send_date' => now()->addMonth()->toDateString(),
            'currency' => $inv->currency,
            'subtotal' => $inv->subtotal,
            'vat_rate' => $inv->vat_rate,
            'vat_amount' => $inv->vat_amount,
            'total' => $inv->total,
            'vat_type' => $inv->vat_type,
            'language' => $inv->language,
            'notes' => $inv->notes,
            'active' => true,
        ]);

        foreach ($inv->items as $item) {
            $recurring->items()->create([
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => round((float) $item->quantity * (float) $item->unit_price, 2),
            ]);
        }

        return redirect()->route('recurring-invoices.edit', $recurring)
            ->with('success', 'Recurring template created. Review and activate it below.');
    })->name('invoices.make-recurring');

    // Invoice portal link generation
    Route::post('/invoices/{invoice}/portal-link', function ($invoiceId) {
        $inv = \App\Models\Invoice::findOrFail($invoiceId);
        if ($inv->user_id !== auth()->id()) {
            abort(403);
        }

        $password = request('portal_password') ?: null;
        $expiryDays = request('portal_expiry') ? (int) request('portal_expiry') : null;

        if ($expiryDays !== null && ($expiryDays < 1 || $expiryDays > 365)) {
            return back()->withErrors(['portal_expiry' => 'Expiry must be between 1 and 365 days.']);
        }

        $token = $inv->generatePortalLink($password, $expiryDays ? now()->addDays($expiryDays) : null);

        return back()->with('portal_url', route('invoice.portal', $token->token));
    })->name('invoices.portal-link');

    // Invoice PDF
    Route::get('/invoices/{invoice}/pdf', function ($invoice) {
        $invoice = \App\Models\Invoice::with(['client', 'items', 'user', 'user.currentCompany'])->findOrFail($invoice);
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }
        $company = $invoice->user->currentCompany;
        $lang = $invoice->language ?? 'en';
        $previousLocale = app()->getLocale();
        app()->setLocale($lang);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('invoice', 'lang', 'company'));
        app()->setLocale($previousLocale);

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    })->name('invoices.pdf');

    // Invoice UBL 2.1 XML (Peppol BIS Billing 3.0)
    Route::get('/invoices/{invoice}/xml', function ($invoiceId) {
        $invoice = \App\Models\Invoice::with(['client', 'items', 'user', 'user.currentCompany'])->findOrFail($invoiceId);
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $xml = app(\App\Services\UblXmlService::class)->generate($invoice);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="invoice-'.$invoice->invoice_number.'.xml"',
        ]);
    })->name('invoices.xml');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/invoices/{invoice}/payment-link', [BillingController::class, 'createPaymentLink'])->name('invoices.payment-link');

    // Push subscriptions
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');
});

// Public invoice view (for payment links)
Route::get('/pay/{invoice}', function ($invoice) {
    $invoice = \App\Models\Invoice::with(['client', 'items'])->findOrFail($invoice);

    return view('invoices.pay', compact('invoice'));
})->name('invoices.pay');

// Client invoice portal (public, token-based)
Route::get('/portal/{token}', [InvoicePortalController::class, 'show'])->name('invoice.portal');
Route::post('/portal/{token}/auth', [InvoicePortalController::class, 'authenticate'])->name('invoice.portal.auth');

// Stripe webhooks (public — no CSRF, no auth)
Route::post('/billing/webhook', [StripeWebhookController::class, 'handle'])->name('billing.webhook');

require __DIR__.'/auth.php';
