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
            'account.updated' => $this->handleAccountUpdated($data),
            'account.application.deauthorized' => $this->handleAccountDeauthorized($data),
            default => null,
        };

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(mixed $session): void
    {
        $type = is_array($session) ? ($session['metadata']['type'] ?? null) : ($session->metadata->type ?? null);

        // Invoice payment: mark invoice as paid
        if ($type === 'invoice_payment') {
            $invoiceId = is_array($session) ? ($session['metadata']['invoice_id'] ?? null) : ($session->metadata->invoice_id ?? null);

            if (! $invoiceId) {
                return;
            }

            $invoice = \App\Models\Invoice::find($invoiceId);
            if (! $invoice || $invoice->status === 'paid') {
                return;
            }

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            Log::info('Invoice marked as paid via Stripe checkout.', ['invoice_id' => $invoice->id]);

            return;
        }

        // Subscription checkout
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
        $cancelAtPeriodEnd = is_array($subscription) ? ($subscription['cancel_at_period_end'] ?? false) : ($subscription->cancel_at_period_end ?? false);

        if (! $customerId) {
            return;
        }

        $user = User::where('stripe_customer_id', $customerId)->first();

        if (! $user) {
            return;
        }

        $updates = [];

        // When cancel_at_period_end is set (portal cancellation OR modal "at period end"),
        // mark as canceled so the UI reflects the pending cancellation immediately.
        // Otherwise sync the status Stripe reports.
        if ($cancelAtPeriodEnd) {
            $updates['subscription_status'] = 'canceled';
        } else {
            $updates['subscription_status'] = $status;
        }

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

    /**
     * Handle account.updated from Stripe Connect.
     * Marks the user's connected account as onboarded when Stripe enables charges.
     */
    private function handleAccountUpdated(mixed $account): void
    {
        $accountId = is_array($account) ? ($account['id'] ?? null) : ($account->id ?? null);
        $chargesEnabled = is_array($account) ? ($account['charges_enabled'] ?? false) : ($account->charges_enabled ?? false);
        $detailsSubmitted = is_array($account) ? ($account['details_submitted'] ?? false) : ($account->details_submitted ?? false);

        if (! $accountId) {
            return;
        }

        $user = User::where('stripe_connect_id', $accountId)->first();

        if (! $user) {
            return;
        }

        if ($chargesEnabled && $detailsSubmitted && ! $user->stripe_connect_onboarded) {
            $user->update(['stripe_connect_onboarded' => true]);
            Log::info('Stripe Connect account onboarding completed for user.', ['user_id' => $user->id]);
        } elseif (! $chargesEnabled && $user->stripe_connect_onboarded) {
            // Account was restricted or disabled
            $user->update(['stripe_connect_onboarded' => false]);
            Log::warning('Stripe Connect account disabled for user.', ['user_id' => $user->id]);
        }
    }

    /**
     * Handle account.application.deauthorized — user revoked access from their Stripe dashboard.
     * Clear Connect fields locally so the user can re-onboard if desired.
     */
    private function handleAccountDeauthorized(mixed $account): void
    {
        $accountId = is_array($account) ? ($account['id'] ?? null) : ($account->id ?? null);

        if (! $accountId) {
            return;
        }

        $user = User::where('stripe_connect_id', $accountId)->first();

        if (! $user) {
            return;
        }

        $user->update([
            'stripe_connect_id' => null,
            'stripe_connect_onboarded' => false,
        ]);

        Log::info('Stripe Connect account deauthorized for user.', ['user_id' => $user->id]);
    }
}
