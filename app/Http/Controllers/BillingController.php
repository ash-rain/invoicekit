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

    public function index(): View
    {
        $user = Auth::user();
        $plan = $user->plan;

        $clientCount = $user->clients()->count();
        $invoicesThisMonth = $user->invoices()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $planData = $this->planService->getPlan($user);

        $billingHistory = [];
        $stripeKey = config('services.stripe.key');
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
            'success_url' => route('billing.index').'?checkout=success',
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
            'return_url' => route('billing.index'),
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

        $stripeKey = config('services.stripe.key');
        if (! $stripeKey) {
            return back()->with('error', 'Stripe is not configured. Please contact support.');
        }

        $stripe = new StripeClient($stripeKey);

        $amountCents = (int) round((float) $invoice->total * 100);
        $currency = strtolower($invoice->currency);

        $price = $stripe->prices->create([
            'unit_amount' => $amountCents,
            'currency' => $currency,
            'product_data' => [
                'name' => __('Invoice').' '.$invoice->invoice_number,
            ],
        ]);

        $paymentLink = $stripe->paymentLinks->create([
            'line_items' => [[
                'price' => $price->id,
                'quantity' => 1,
            ]],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => ['url' => route('invoices.show', $invoice)],
            ],
            'metadata' => [
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
            ],
        ]);

        $invoice->update(['stripe_payment_link_url' => $paymentLink->url]);

        return back()->with('success', __('Payment link created.'));
    }
}
