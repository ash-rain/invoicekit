<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Clients\ClientList;
use App\Livewire\Clients\CreateEditClient;
use App\Livewire\Dashboard;
use App\Livewire\Invoices\CreateInvoice;
use App\Livewire\Invoices\InvoiceList;
use App\Livewire\OnboardingWizard;
use App\Livewire\Projects\CreateEditProject;
use App\Livewire\Projects\ProjectDetail;
use App\Livewire\Projects\ProjectList;
use Illuminate\Support\Facades\Route;

// ── Landing page ─────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});

// ── Legal pages ───────────────────────────────────────────────────────────────
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');
Route::get('/terms', fn() => view('legal.terms'))->name('terms');

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
    $content .= "Disallow: /profile\n";
    $content .= "Disallow: /clients\n";
    $content .= "Disallow: /projects\n";
    $content .= "Disallow: /invoices\n";
    $content .= "Disallow: /timer\n";
    $content .= "Disallow: /billing\n";
    $content .= "Disallow: /onboarding\n";
    $content .= 'Sitemap: ' . route('sitemap') . "\n";

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

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
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
    Route::get('/timer', fn() => view('timer.index'))->name('timer');

    // Invoices
    Route::get('/invoices', InvoiceList::class)->name('invoices.index');
    Route::get('/invoices/create', CreateInvoice::class)->name('invoices.create');
    Route::get('/invoices/{invoice}', function (\App\Models\Invoice $invoice) {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        return view('invoices.show', compact('invoice'));
    })->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', CreateInvoice::class)->name('invoices.edit');

    // Invoice PDF
    Route::get('/invoices/{invoice}/pdf', function ($invoice) {
        $invoice = \App\Models\Invoice::with(['client', 'items', 'user'])->findOrFail($invoice);
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }
        $lang = $invoice->language ?? 'en';
        $previousLocale = app()->getLocale();
        app()->setLocale($lang);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('invoice', 'lang'));
        app()->setLocale($previousLocale);

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    })->name('invoices.pdf');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
});

// Public invoice view (for payment links)
Route::get('/pay/{invoice}', function ($invoice) {
    $invoice = \App\Models\Invoice::with(['client', 'items'])->findOrFail($invoice);

    return view('invoices.pay', compact('invoice'));
})->name('invoices.pay');

require __DIR__ . '/auth.php';
