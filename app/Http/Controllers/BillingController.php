<?php

namespace App\Http\Controllers;

use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Stripe\StripeClient;

class BillingController extends Controller
{
    public function __construct(private readonly PlanService $planService) {}

    public function index(Request $request): View
    {
        $user = Auth::user();

        // When returning from Stripe Checkout, verify the session and sync plan immediately.
        // This handles the case where webhooks are delayed or not yet configured.
        $stripeKey = config('services.stripe.key');
        if ($request->query('checkout') === 'success' && $request->query('session_id') && $stripeKey) {
            try {
                $stripe = new StripeClient($stripeKey);
                $session = $stripe->checkout->sessions->retrieve($request->query('session_id'), [
                    'expand' => ['subscription'],
                ]);

                if ($session->status === 'complete' && $session->customer === $user->stripe_customer_id) {
                    $plan = $session->metadata->plan ?? 'pro';
                    $subscriptionId = $session->subscription->id ?? $session->subscription ?? null;
                    $status = $session->subscription->status ?? 'active';
                    $periodEnd = $session->subscription->current_period_end ?? null;

                    $user->update([
                        'plan' => in_array($plan, ['starter', 'pro']) ? $plan : 'pro',
                        'subscription_status' => $status,
                        'stripe_subscription_id' => $subscriptionId,
                        'subscribed_until' => $periodEnd ? \Carbon\Carbon::createFromTimestamp($periodEnd) : null,
                    ]);

                    $user->refresh();
                }
            } catch (\Exception) {
                // Silently ignore — the success banner still shows, webhook will sync later.
            }
        }

        // When returning from the Stripe Customer Portal, sync subscription state immediately.
        // This handles cancellations, plan changes, and payment method updates from the portal.
        if ($request->query('from') === 'portal' && $stripeKey && $user->stripe_subscription_id) {
            try {
                $stripe = new StripeClient($stripeKey);
                $subscription = $stripe->subscriptions->retrieve($user->stripe_subscription_id);

                $cancelAtPeriodEnd = $subscription->cancel_at_period_end ?? false;
                $status = $subscription->status ?? null;
                $periodEnd = $subscription->current_period_end ?? null;

                $updates = [
                    'subscription_status' => $cancelAtPeriodEnd ? 'canceled' : $status,
                    'subscribed_until' => $periodEnd ? \Carbon\Carbon::createFromTimestamp($periodEnd) : null,
                ];

                if ($status === 'active') {
                    $priceId = $subscription->items->data[0]->price->id ?? null;
                    $starterPriceId = config('services.stripe.starter_price_id');
                    $updates['plan'] = ($priceId && $priceId === $starterPriceId) ? 'starter' : 'pro';
                }

                $user->update($updates);
                $user->refresh();
            } catch (\Exception) {
                // Silently ignore — webhook will sync later.
            }
        }

        $plan = $user->plan;

        $clientCount = $user->clients()->count();
        $invoicesThisMonth = $user->invoices()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $planData = $this->planService->getPlan($user);

        $billingHistory = [];
        if ($stripeKey && $user->stripe_customer_id) {
            try {
                $stripe = new StripeClient($stripeKey);
                $stripeInvoices = $stripe->invoices->all([
                    'customer' => $user->stripe_customer_id,
                    'limit' => 24,
                ]);
                $billingHistory = $stripeInvoices->data;
            } catch (\Exception) {
                $billingHistory = [];
            }
        }

        return view('billing.index', [
            'plan' => $plan,
            'clientCount' => $clientCount,
            'invoicesThisMonth' => $invoicesThisMonth,
            'clientsLimit' => $planData['clients_limit'],
            'invoicesLimit' => $planData['invoices_per_month_limit'],
            'aiImportsToday' => $this->planService->aiImportsTodayCount($user),
            'aiImportsLimit' => $this->planService->aiImportDailyLimit($user),
            'user' => $user,
            'billingHistory' => $billingHistory,
        ]);
    }

    public function checkout(Request $request, string $plan): RedirectResponse
    {
        $validPlans = ['starter', 'pro'];
        if (! in_array($plan, $validPlans)) {
            abort(404);
        }

        $stripeKey = config('services.stripe.key');
        if (! $stripeKey) {
            return back()->with('error', 'Stripe is not configured. Please contact support.');
        }

        $priceId = $plan === 'pro'
            ? config('services.stripe.pro_price_id')
            : config('services.stripe.starter_price_id');

        if (! $priceId) {
            return back()->with('error', 'Stripe price not configured for this plan.');
        }

        $user = Auth::user();

        $stripe = new StripeClient($stripeKey);

        // Create or retrieve Stripe customer
        if (! $user->stripe_customer_id) {
            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => ['user_id' => $user->id],
            ]);
            $user->update(['stripe_customer_id' => $customer->id]);
        }

        $session = $stripe->checkout->sessions->create([
            'customer' => $user->stripe_customer_id,
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => route('billing.index').'?checkout=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('billing.index').'?checkout=cancelled',
            'metadata' => ['user_id' => $user->id, 'plan' => $plan],
        ]);

        return redirect($session->url);
    }

    public function portal(Request $request): RedirectResponse
    {
        $stripeKey = config('services.stripe.key');
        if (! $stripeKey) {
            return back()->with('error', 'Stripe is not configured. Please contact support.');
        }

        $user = Auth::user();
        if (! $user->stripe_customer_id) {
            return back()->with('error', 'No billing account found.');
        }

        $stripe = new StripeClient($stripeKey);

        $session = $stripe->billingPortal->sessions->create([
            'customer' => $user->stripe_customer_id,
            'return_url' => route('billing.index').'?from=portal',
        ]);

        return redirect($session->url);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->validate([
            'cancel_at_period_end' => ['required', 'in:0,1'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();

        if (! $user->stripe_subscription_id) {
            return back()->with('error', 'No active subscription found.');
        }

        $stripeKey = config('services.stripe.key');
        if (! $stripeKey) {
            return back()->with('error', 'Stripe is not configured. Please contact support.');
        }

        $stripe = new StripeClient($stripeKey);
        $cancelAtPeriodEnd = (bool) $request->input('cancel_at_period_end', true);

        if ($cancelAtPeriodEnd) {
            $subscription = $stripe->subscriptions->update($user->stripe_subscription_id, [
                'cancel_at_period_end' => true,
            ]);
            $user->update([
                'subscription_status' => 'canceled',
                'subscribed_until' => \Carbon\Carbon::createFromTimestamp($subscription->current_period_end),
            ]);

            return back()->with('success', __('Your subscription will be cancelled at the end of the current billing period.'));
        }

        $stripe->subscriptions->cancel($user->stripe_subscription_id);
        $user->update([
            'plan' => 'free',
            'subscription_status' => 'canceled',
            'stripe_subscription_id' => null,
            'subscribed_until' => null,
        ]);

        return back()->with('success', __('Your subscription has been cancelled.'));
    }

    public function createPaymentLink(Request $request, int $invoice): RedirectResponse
    {
        $invoice = \App\Models\Invoice::findOrFail($invoice);

        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            return back()->with('error', __('This invoice is already paid.'));
        }

        $user = Auth::user();

        if (! $user->hasStripeConnect()) {
            return back()->with('error', __('Connect your Stripe account in Settings → Payments before generating payment links.'));
        }

        $stripeKey = config('services.stripe.key');
        if (! $stripeKey) {
            return back()->with('error', 'Stripe is not configured. Please contact support.');
        }

        $stripe = new StripeClient($stripeKey);

        $amountCents = (int) round((float) $invoice->total * 100);
        $currency = strtolower($invoice->currency);
        $feePercent = (float) config('services.stripe.application_fee_percent', 2);
        $feeAmount = (int) round($amountCents * $feePercent / 100);

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $amountCents,
                    'product_data' => [
                        'name' => __('Invoice').' '.$invoice->invoice_number,
                    ],
                ],
                'quantity' => 1,
            ]],
            'payment_intent_data' => [
                'application_fee_amount' => $feeAmount,
                'transfer_data' => [
                    'destination' => $user->stripe_connect_id,
                ],
            ],
            'success_url' => route('invoices.show', $invoice->id).'?payment=success',
            'cancel_url' => route('invoices.show', $invoice->id).'?payment=cancelled',
            'metadata' => [
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
                'type' => 'invoice_payment',
            ],
        ]);

        $invoice->update(['stripe_payment_link_url' => $session->url]);

        return back()->with('success', __('Payment link created.'));
    }
}
