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

        return view('billing.index', [
            'plan' => $plan,
            'clientCount' => $clientCount,
            'invoicesThisMonth' => $invoicesThisMonth,
            'clientsLimit' => $planData['clients_limit'],
            'invoicesLimit' => $planData['invoices_per_month_limit'],
            'user' => $user,
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
}
