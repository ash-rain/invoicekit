<?php

namespace App\Services;

use App\Models\User;

class PlanService
{
    /**
     * Plan definitions: limits per plan.
     *
     * null means unlimited.
     */
    public const PLANS = [
        'free' => [
            'label' => 'Free',
            'price' => 0,
            'clients_limit' => 3,
            'invoices_per_month_limit' => 5,
            'payment_methods_limit' => 1,
            'recurring_invoices' => false,
            'client_portal' => false,
        ],
        'starter' => [
            'label' => 'Starter',
            'price' => 9,
            'clients_limit' => null,
            'invoices_per_month_limit' => 20,
            'payment_methods_limit' => 3,
            'recurring_invoices' => false,
            'client_portal' => false,
        ],
        'pro' => [
            'label' => 'Pro',
            'price' => 29,
            'clients_limit' => null,
            'invoices_per_month_limit' => null,
            'payment_methods_limit' => null,
            'recurring_invoices' => true,
            'client_portal' => true,
        ],
    ];

    public function getPlan(User $user): array
    {
        return self::PLANS[$user->plan] ?? self::PLANS['free'];
    }

    public function canAddClient(User $user): bool
    {
        $plan = $this->getPlan($user);
        if ($plan['clients_limit'] === null) {
            return true;
        }

        return $user->clients()->count() < $plan['clients_limit'];
    }

    public function canCreateInvoice(User $user): bool
    {
        $plan = $this->getPlan($user);
        if ($plan['invoices_per_month_limit'] === null) {
            return true;
        }

        $count = $user->invoices()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $count < $plan['invoices_per_month_limit'];
    }

    public function clientsRemaining(User $user): ?int
    {
        $plan = $this->getPlan($user);
        if ($plan['clients_limit'] === null) {
            return null;
        }

        return max(0, $plan['clients_limit'] - $user->clients()->count());
    }

    public function invoicesRemainingThisMonth(User $user): ?int
    {
        $plan = $this->getPlan($user);
        if ($plan['invoices_per_month_limit'] === null) {
            return null;
        }

        $count = $user->invoices()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return max(0, $plan['invoices_per_month_limit'] - $count);
    }

    public function canAddPaymentMethod(User $user): bool
    {
        $plan = $this->getPlan($user);
        if ($plan['payment_methods_limit'] === null) {
            return true;
        }

        $count = $user->currentCompany?->paymentMethods()->count() ?? 0;

        return $count < $plan['payment_methods_limit'];
    }

    public function paymentMethodsRemaining(User $user): ?int
    {
        $plan = $this->getPlan($user);
        if ($plan['payment_methods_limit'] === null) {
            return null;
        }

        $count = $user->currentCompany?->paymentMethods()->count() ?? 0;

        return max(0, $plan['payment_methods_limit'] - $count);
    }

    public function aiImportDailyLimit(User $user): ?int
    {
        if ($user->gemini_api_key) {
            return null;
        }

        $limits = config('ai.limits', []);
        $plan = $user->plan ?? 'free';

        if (array_key_exists($plan, $limits)) {
            return $limits[$plan];
        }

        return $limits['free'] ?? 2;
    }

    public function aiImportsTodayCount(User $user): int
    {
        return $user->documentImports()
            ->whereDate('created_at', today())
            ->where('used_own_key', false)
            ->count();
    }

    public function canImportDocument(User $user): bool
    {
        if ($user->gemini_api_key) {
            return true;
        }

        $limit = $this->aiImportDailyLimit($user);

        if ($limit === null) {
            return true;
        }

        return $this->aiImportsTodayCount($user) < $limit;
    }

    public function aiImportsRemainingToday(User $user): ?int
    {
        $limit = $this->aiImportDailyLimit($user);

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->aiImportsTodayCount($user));
    }
}
