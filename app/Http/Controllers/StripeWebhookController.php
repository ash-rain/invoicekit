<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if ($secret) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
            } catch (SignatureVerificationException $e) {
                Log::warning('Stripe webhook signature verification failed.', ['error' => $e->getMessage()]);

                return response('Invalid signature', 400);
            }
        } else {
            // No webhook secret — parse JSON as nested objects for consistent access
            $event = json_decode($payload);
        }

        $type = $event->type ?? null;
        $data = $event->data->object ?? null;

        match ($type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($data),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($data),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($data),
            'invoice.payment_failed' => $this->handlePaymentFailed($data),
            default => null,
        };

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(mixed $session): void
    {
        $customerId = is_array($session) ? ($session['customer'] ?? null) : ($session->customer ?? null);
        $subscriptionId = is_array($session) ? ($session['subscription'] ?? null) : ($session->subscription ?? null);
        $plan = is_array($session) ? ($session['metadata']['plan'] ?? 'pro') : ($session->metadata->plan ?? 'pro');

        if (! $customerId) {
            return;
        }

        $user = User::where('stripe_customer_id', $customerId)->first();

        if (! $user) {
            return;
        }

        $user->update([
            'stripe_subscription_id' => $subscriptionId,
            'subscription_status' => 'active',
            'plan' => in_array($plan, ['starter', 'pro']) ? $plan : 'pro',
        ]);

        Log::info('Stripe checkout completed for user.', ['user_id' => $user->id, 'plan' => $plan]);
    }

    private function handleSubscriptionUpdated(mixed $subscription): void
    {
        $customerId = is_array($subscription) ? ($subscription['customer'] ?? null) : ($subscription->customer ?? null);
        $status = is_array($subscription) ? ($subscription['status'] ?? null) : ($subscription->status ?? null);
        $currentPeriodEnd = is_array($subscription) ? ($subscription['current_period_end'] ?? null) : ($subscription->current_period_end ?? null);

        if (! $customerId) {
            return;
        }

        $user = User::where('stripe_customer_id', $customerId)->first();

        if (! $user) {
            return;
        }

        $updates = [
            'subscription_status' => $status,
        ];

        if ($currentPeriodEnd) {
            $updates['subscribed_until'] = Carbon::createFromTimestamp($currentPeriodEnd);
        }

        if ($status === 'active') {
            $priceId = is_array($subscription)
                ? ($subscription['items']['data'][0]['price']['id'] ?? null)
                : ($subscription->items->data[0]->price->id ?? null);

            $starterPriceId = config('services.stripe.starter_price_id');
            $updates['plan'] = ($priceId && $priceId === $starterPriceId) ? 'starter' : 'pro';
        }

        $user->update($updates);

        Log::info('Stripe subscription updated.', ['user_id' => $user->id, 'status' => $status]);
    }

    private function handleSubscriptionDeleted(mixed $subscription): void
    {
        $customerId = is_array($subscription) ? ($subscription['customer'] ?? null) : ($subscription->customer ?? null);

        if (! $customerId) {
            return;
        }

        $user = User::where('stripe_customer_id', $customerId)->first();

        if (! $user) {
            return;
        }

        $user->update([
            'plan' => 'free',
            'subscription_status' => 'canceled',
            'stripe_subscription_id' => null,
            'subscribed_until' => null,
        ]);

        Log::info('Stripe subscription deleted for user.', ['user_id' => $user->id]);
    }

    private function handlePaymentFailed(mixed $invoice): void
    {
        $customerId = is_array($invoice) ? ($invoice['customer'] ?? null) : ($invoice->customer ?? null);

        if (! $customerId) {
            return;
        }

        $user = User::where('stripe_customer_id', $customerId)->first();

        if (! $user) {
            return;
        }

        // Update status to past_due; downgrade happens when subscription is deleted
        $user->update(['subscription_status' => 'past_due']);

        // Notify user of failed payment
        try {
            Mail::to($user->email)->send(new \App\Mail\PaymentFailedNotification($user));
        } catch (\Exception $e) {
            Log::error('Failed to send payment_failed email.', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        Log::warning('Stripe payment failed for user.', ['user_id' => $user->id]);
    }
}
