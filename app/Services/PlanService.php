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
            'recurring_invoices' => false,
            'client_portal' => false,
        ],
        'starter' => [
            'label' => 'Starter',
            'price' => 15,
            'clients_limit' => null,
            'invoices_per_month_limit' => 20,
            'recurring_invoices' => false,
            'client_portal' => false,
        ],
        'pro' => [
            'label' => 'Pro',
            'price' => 29,
            'clients_limit' => null,
            'invoices_per_month_limit' => null,
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
}
