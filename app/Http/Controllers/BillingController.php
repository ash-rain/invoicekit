<?php

namespace App\Http\Controllers;

use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
        ]);
    }

    public function checkout(Request $request, string $plan): RedirectResponse
    {
        $validPlans = ['starter', 'pro'];
        if (! in_array($plan, $validPlans)) {
            abort(404);
        }

        // In a real integration this would create a Stripe Checkout session.
        // For now we redirect back with a message about Stripe configuration.
        if (! config('cashier.key') && ! config('services.stripe.key')) {
            return back()->with('error', 'Stripe is not configured. Please contact support.');
        }

        return back()->with('info', 'Redirecting to Stripe Checkout…');
    }

    public function portal(Request $request): RedirectResponse
    {
        // In a real integration this would redirect to the Stripe Customer Portal.
        if (! config('cashier.key') && ! config('services.stripe.key')) {
            return back()->with('error', 'Stripe is not configured. Please contact support.');
        }

        return back()->with('info', 'Redirecting to Stripe Customer Portal…');
    }
}
