<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

class StripeConnectController extends Controller
{
    /**
     * Start the Express onboarding flow. Creates a connected account if needed,
     * then redirects to Stripe-hosted onboarding.
     */
    public function onboard(Request $request): RedirectResponse
    {
        $stripeKey = config('services.stripe.key');
        if (! $stripeKey) {
            return back()->with('error', 'Stripe is not configured. Please contact support.');
        }

        $user = Auth::user();
        $stripe = new StripeClient($stripeKey);

        // Create Express account if not yet created
        if (! $user->stripe_connect_id) {
            $account = $stripe->accounts->create([
                'type' => 'express',
                'email' => $user->email,
                'metadata' => ['user_id' => $user->id],
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
            ]);

            $user->update(['stripe_connect_id' => $account->id]);
        }

        $accountLink = $stripe->accountLinks->create([
            'account' => $user->stripe_connect_id,
            'refresh_url' => route('stripe-connect.refresh'),
            'return_url' => route('stripe-connect.callback'),
            'type' => 'account_onboarding',
        ]);

        return redirect($accountLink->url);
    }

    /**
     * Return URL after Stripe onboarding completes. Verifies account status
     * and marks the user as onboarded if Stripe has enabled charges.
     */
    public function callback(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->stripe_connect_id) {
            return redirect()->route('settings.index', ['tab' => 'payments'])
                ->with('error', 'Something went wrong during Stripe setup. Please try again.');
        }

        $stripeKey = config('services.stripe.key');
        if (! $stripeKey) {
            return redirect()->route('settings.index', ['tab' => 'payments'])
                ->with('error', 'Stripe is not configured. Please contact support.');
        }

        $stripe = new StripeClient($stripeKey);
        $account = $stripe->accounts->retrieve($user->stripe_connect_id);

        if ($account->charges_enabled && $account->details_submitted) {
            $user->update(['stripe_connect_onboarded' => true]);

            // Auto-create Stripe payment method
            $company = $user->currentCompany;
            if ($company) {
                PaymentMethod::firstOrCreate(
                    ['company_id' => $company->id, 'type' => PaymentMethod::TYPE_STRIPE],
                    [
                        'label' => 'Stripe',
                        'stripe_connect_id' => $user->stripe_connect_id,
                        'is_default' => ! $company->paymentMethods()->exists(),
                    ]
                );
            }

            return redirect()->route('settings.index', ['tab' => 'payments'])
                ->with('success', 'Your Stripe account is connected and ready to accept payments.');
        }

        return redirect()->route('settings.index', ['tab' => 'payments'])
            ->with('warning', 'Your Stripe account setup is incomplete. Please finish onboarding to accept payments.');
    }

    /**
     * Refresh URL — called when the Account Link expires. Generates a new one
     * and redirects the user back to Stripe.
     */
    public function refresh(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->stripe_connect_id) {
            return redirect()->route('settings.index', ['tab' => 'payments'])
                ->with('error', 'No Stripe account found. Please start onboarding again.');
        }

        $stripeKey = config('services.stripe.key');
        if (! $stripeKey) {
            return redirect()->route('settings.index', ['tab' => 'payments'])
                ->with('error', 'Stripe is not configured. Please contact support.');
        }

        $stripe = new StripeClient($stripeKey);
        $accountLink = $stripe->accountLinks->create([
            'account' => $user->stripe_connect_id,
            'refresh_url' => route('stripe-connect.refresh'),
            'return_url' => route('stripe-connect.callback'),
            'type' => 'account_onboarding',
        ]);

        return redirect($accountLink->url);
    }

    /**
     * Redirect the user to their Stripe Express dashboard login link.
     */
    public function dashboard(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->hasStripeConnect()) {
            return back()->with('error', 'No connected Stripe account found.');
        }

        $stripeKey = config('services.stripe.key');
        if (! $stripeKey) {
            return back()->with('error', 'Stripe is not configured. Please contact support.');
        }

        $stripe = new StripeClient($stripeKey);
        $loginLink = $stripe->accounts->createLoginLink($user->stripe_connect_id);

        return redirect($loginLink->url);
    }

    /**
     * Disconnect (deauthorize) the connected Stripe account.
     */
    public function disconnect(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->stripe_connect_id) {
            return redirect()->route('settings.index', ['tab' => 'payments'])
                ->with('error', 'No connected Stripe account found.');
        }

        $stripeKey = config('services.stripe.key');

        if ($stripeKey) {
            try {
                $stripe = new StripeClient($stripeKey);
                $stripe->oauth->deauthorize([
                    'client_id' => config('services.stripe.client_id'),
                    'stripe_user_id' => $user->stripe_connect_id,
                ]);
            } catch (\Exception) {
                // Account may already be deauthorized; proceed with local cleanup
            }
        }

        // Remove Stripe payment method
        $company = $user->currentCompany;
        if ($company) {
            $stripeMethod = $company->paymentMethods()
                ->where('type', PaymentMethod::TYPE_STRIPE)
                ->first();

            if ($stripeMethod) {
                $wasDefault = $stripeMethod->is_default;
                $stripeMethod->delete();

                // Promote next method to default if needed
                if ($wasDefault) {
                    $next = $company->paymentMethods()->first();
                    if ($next) {
                        $next->update(['is_default' => true]);
                    }
                }
            }
        }

        $user->update([
            'stripe_connect_id' => null,
            'stripe_connect_onboarded' => false,
        ]);

        return redirect()->route('settings.index', ['tab' => 'payments'])
            ->with('success', 'Stripe account disconnected. Existing payment links will no longer work.');
    }
}
