<?php

namespace Tests\Concerns;

use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

trait InteractsWithStripeWebhooks
{
    /**
     * Post a Stripe webhook event with a valid signature header.
     *
     * Configures a known signing secret and computes the matching
     * `Stripe-Signature` header so the controller's signature verification
     * passes, exercising the real handlers without re-opening the unsigned
     * payload hole the security hardening closed.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function postStripeWebhook(array $payload): TestResponse
    {
        $secret = 'whsec_'.Str::random(32);
        config(['services.stripe.webhook_secret' => $secret]);

        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $secret);

        return $this->call(
            'POST',
            route('billing.webhook'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            $body,
        );
    }
}
