<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceAccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class X402Controller extends Controller
{
    /**
     * Health / discovery endpoint — no payment required.
     * Advertises what payment-gated endpoints are available.
     */
    public function discovery(): JsonResponse
    {
        return response()->json([
            'service' => 'InvoiceKit API',
            'x402' => true,
            'network' => config('x402.network'),
            'facilitator' => config('x402.facilitator_url'),
            'endpoints' => [
                [
                    'path' => '/api/x402/ping',
                    'method' => 'GET',
                    'description' => 'Echo endpoint to test x402 payment flow',
                    'price_usd' => config('x402.price_usd'),
                ],
                [
                    'path' => '/api/x402/invoice/{number}',
                    'method' => 'GET',
                    'description' => 'Fetch invoice data by invoice number (requires portal token header)',
                    'price_usd' => config('x402.price_usd'),
                    'headers_required' => ['X-Portal-Token'],
                ],
            ],
        ]);
    }

    /**
     * Simple paid ping — useful for testing the payment flow end-to-end.
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'message' => 'Payment verified. InvoiceKit API is available.',
            'timestamp' => now()->toIso8601String(),
            'network' => config('x402.network'),
        ]);
    }

    /**
     * Return invoice data by invoice number.
     *
     * Callers must supply an X-Portal-Token header (obtained from the InvoiceKit
     * UI) alongside the x402 payment. This binds the paid access to a specific
     * invoice without exposing private user data.
     */
    public function invoice(Request $request, string $number): JsonResponse
    {
        $portalToken = $request->header('X-Portal-Token');

        if (! $portalToken) {
            return response()->json([
                'error' => 'X-Portal-Token header is required. Generate one from InvoiceKit settings.',
            ], 422);
        }

        $accessToken = InvoiceAccessToken::where('token', $portalToken)
            ->with('invoice.items')
            ->first();

        if (! $accessToken || $accessToken->isExpired()) {
            return response()->json(['error' => 'Invalid or expired portal token.'], 403);
        }

        $invoice = $accessToken->invoice;

        if ($invoice->invoice_number !== $number) {
            return response()->json(['error' => 'Token does not match the requested invoice.'], 403);
        }

        return response()->json([
            'invoice' => [
                'id' => $invoice->id,
                'number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'currency' => $invoice->currency,
                'subtotal' => $invoice->subtotal,
                'vat_rate' => $invoice->vat_rate,
                'vat_amount' => $invoice->vat_amount,
                'total' => $invoice->total,
                'issued_at' => $invoice->created_at?->toDateString(),
                'due_at' => $invoice->due_date?->toDateString(),
                'paid_at' => $invoice->paid_at?->toDateString(),
                'notes' => $invoice->notes,
                'items' => $invoice->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]),
            ],
        ]);
    }
}
