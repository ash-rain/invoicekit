<?php

use App\Http\Controllers\X402Controller;
use App\Http\Middleware\X402PaymentMiddleware;
use Illuminate\Support\Facades\Route;

// ── x402 API ─────────────────────────────────────────────────────────────────
// Discovery endpoint — no payment required
Route::get('/x402', [X402Controller::class, 'discovery'])->name('x402.discovery');

// Payment-gated endpoints
Route::middleware(X402PaymentMiddleware::class)->group(function () {
    Route::get('/x402/ping', [X402Controller::class, 'ping'])->name('x402.ping');
    Route::get('/x402/invoice/{number}', [X402Controller::class, 'invoice'])->name('x402.invoice');
});
