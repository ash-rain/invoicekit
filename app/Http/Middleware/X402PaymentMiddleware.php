<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class X402PaymentMiddleware
{
    /**
     * Price per call in USDC atomic units (1 USDC = 1_000_000 units).
     */
    private function priceInAtomicUnits(): string
    {
        $usd = config('x402.price_usd', 0.001);

        return (string) (int) round($usd * 1_000_000);
    }

    /**
     * Build the payment requirements payload for the 402 response.
     *
     * @return array<string, mixed>
     */
    private function buildPaymentRequirements(Request $request): array
    {
        return [
            'scheme' => 'exact',
            'network' => config('x402.network'),
            'amount' => $this->priceInAtomicUnits(),
            'asset' => config('x402.asset'),
            'payTo' => config('x402.pay_to'),
            'maxTimeoutSeconds' => config('x402.max_timeout_seconds', 300),
            'resource' => $request->url(),
            'description' => 'InvoiceKit API access',
            'mimeType' => 'application/json',
            'extra' => [
                'assetTransferMethod' => 'eip3009',
                'name' => 'USDC',
                'version' => '2',
            ],
        ];
    }

    /**
     * Return an HTTP 402 response with encoded payment requirements.
     */
    private function paymentRequired(Request $request): Response
    {
        $requirements = $this->buildPaymentRequirements($request);

        $body = [
            'x402Version' => 2,
            'error' => 'Payment required',
            'resource' => [
                'url' => $request->url(),
                'description' => 'InvoiceKit API access',
                'mimeType' => 'application/json',
            ],
            'accepts' => [$requirements],
            'extensions' => null,
        ];

        return response()->json($body, 402)
            ->header('PAYMENT-REQUIRED', base64_encode(json_encode($body)));
    }

    public function handle(Request $request, Closure $next): Response
    {
        // x402 requires the wallet address to be configured
        if (! config('x402.pay_to')) {
            Log::warning('x402: X402_PAY_TO is not configured. Payment gating is disabled.');

            return $next($request);
        }

        $paymentHeader = $request->header('PAYMENT-SIGNATURE');

        if (! $paymentHeader) {
            return $this->paymentRequired($request);
        }

        $paymentPayload = json_decode(base64_decode($paymentHeader), true);

        if (! $paymentPayload || ! isset($paymentPayload['scheme'])) {
            return $this->paymentRequired($request);
        }

        $requirements = $this->buildPaymentRequirements($request);

        // Verify the payment with the facilitator
        $facilitatorUrl = rtrim(config('x402.facilitator_url'), '/');
        $timeout = config('x402.facilitator_timeout', 10);

        $verifyBody = [
            'x402Version' => 2,
            'paymentPayload' => $paymentPayload,
            'paymentRequirements' => $requirements,
        ];

        try {
            $verifyResponse = Http::timeout($timeout)
                ->retry(2, 500)
                ->post("{$facilitatorUrl}/verify", $verifyBody);
        } catch (\Exception $e) {
            Log::error('x402: facilitator /verify failed.', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Payment verification unavailable. Please retry.'], 503);
        }

        $verification = $verifyResponse->json();

        if (! ($verification['isValid'] ?? false)) {
            Log::info('x402: invalid payment.', ['reason' => $verification['invalidReason'] ?? 'unknown']);

            return $this->paymentRequired($request);
        }

        /** @var Response $response */
        $response = $next($request);

        // Settle the payment (best-effort; log failures but do not block the response)
        try {
            $settleResponse = Http::timeout($timeout)
                ->post("{$facilitatorUrl}/settle", $verifyBody);

            $settled = $settleResponse->json();

            if ($settled['success'] ?? false) {
                $paymentResponseHeader = base64_encode(json_encode($settled));
                $response->headers->set('PAYMENT-RESPONSE', $paymentResponseHeader);

                Log::info('x402: payment settled.', [
                    'payer' => $settled['payer'] ?? null,
                    'tx' => $settled['transaction'] ?? null,
                ]);
            } else {
                Log::warning('x402: settlement failed.', ['reason' => $settled['errorReason'] ?? 'unknown']);
            }
        } catch (\Exception $e) {
            Log::error('x402: facilitator /settle failed.', ['error' => $e->getMessage()]);
        }

        return $response;
    }
}
