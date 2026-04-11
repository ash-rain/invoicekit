<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SetupGuide extends Component
{
    /**
     * Returns the ordered list of setup steps.
     *
     * Each step has:
     *   key          — unique identifier (stored in dismissed_steps JSON)
     *   title        — translation key shown as the step heading
     *   description  — translation key shown as supporting text
     *   url          — deep-link URL the CTA button points to
     *   cta          — translation key for the CTA button label
     *   auto_detect  — whether the step can be auto-detected as complete
     *   dismissible  — whether the user can manually skip/dismiss the step
     *
     * @return array<int, array<string, mixed>>
     */
    public static function steps(): array
    {
        return [
            [
                'key' => 'business_profile',
                'title' => 'Complete your business profile',
                'description' => 'Add your company name, address, and country',
                'url' => '/settings?tab=business',
                'cta' => 'Open Settings',
                'auto_detect' => true,
                'dismissible' => false,
            ],
            [
                'key' => 'invoicing_defaults',
                'title' => 'Set up invoicing defaults',
                'description' => 'Configure your default currency and payment terms',
                'url' => '/settings?tab=invoicing',
                'cta' => 'Open Settings',
                'auto_detect' => true,
                'dismissible' => false,
            ],
            [
                'key' => 'payment_method',
                'title' => 'Add a payment method',
                'description' => 'Add your IBAN or connect Stripe so clients know how to pay you',
                'url' => '/settings?tab=business',
                'cta' => 'Add Payment Method',
                'auto_detect' => true,
                'dismissible' => true,
            ],
            [
                'key' => 'connect_stripe',
                'title' => 'Connect Stripe for online payments',
                'description' => 'Accept credit card payments from your clients',
                'url' => '/settings?tab=payments',
                'cta' => 'Connect Stripe',
                'auto_detect' => true,
                'dismissible' => true,
            ],
            [
                'key' => 'ai_services',
                'title' => 'Add your AI API key',
                'description' => 'Enable AI-powered document imports with your Gemini key',
                'url' => '/settings?tab=ai',
                'cta' => 'Add API Key',
                'auto_detect' => true,
                'dismissible' => true,
            ],
            [
                'key' => 'import_invoices',
                'title' => 'Import your existing invoices',
                'description' => 'Migrate invoices from your previous software',
                'url' => '/invoices/import',
                'cta' => 'Import Invoices',
                'auto_detect' => false,
                'dismissible' => true,
            ],
            [
                'key' => 'import_expenses',
                'title' => 'Import your existing expenses',
                'description' => 'Migrate expenses from your previous software',
                'url' => '/expenses/import',
                'cta' => 'Import Expenses',
                'auto_detect' => false,
                'dismissible' => true,
            ],
        ];
    }

    public function isStepCompleted(string $key): bool
    {
        $user = Auth::user();

        if ($user->hasSetupGuideStepDismissed($key)) {
            return true;
        }

        return match ($key) {
            'business_profile' => $this->isBusinessProfileComplete($user),
            'invoicing_defaults' => $this->isInvoicingDefaultsComplete($user),
            'payment_method' => $this->hasPaymentMethod($user),
            'connect_stripe' => $user->hasStripeConnect(),
            'ai_services' => $user->gemini_api_key !== null,
            default => false,
        };
    }

    public function completedCount(): int
    {
        return collect(self::steps())
            ->filter(fn (array $step) => $this->isStepCompleted($step['key']))
            ->count();
    }

    public function progressPercent(): int
    {
        $total = count(self::steps());

        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->completedCount() / $total) * 100);
    }

    public function allStepsCompleted(): bool
    {
        return $this->completedCount() === count(self::steps());
    }

    public function dismissStep(string $key): void
    {
        $step = collect(self::steps())->firstWhere('key', $key);

        if ($step === null || ! $step['dismissible']) {
            return;
        }

        $user = Auth::user();
        $dismissed = $user->setup_guide_dismissed_steps ?? [];

        if (! in_array($key, $dismissed)) {
            $dismissed[] = $key;
            $user->update(['setup_guide_dismissed_steps' => $dismissed]);
        }
    }

    public function dismissGuide(): void
    {
        Auth::user()->update(['setup_guide_dismissed_at' => now()]);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();
        $steps = self::steps();

        return view('livewire.setup-guide', [
            'steps' => $steps,
            'user' => $user,
            'completedCount' => $this->completedCount(),
            'totalCount' => count($steps),
            'progressPercent' => $this->progressPercent(),
        ]);
    }

    private function isBusinessProfileComplete(\App\Models\User $user): bool
    {
        $company = $user->currentCompany;

        if ($company === null) {
            return false;
        }

        return filled($company->name)
            && filled($company->address_line1)
            && filled($company->country);
    }

    private function isInvoicingDefaultsComplete(\App\Models\User $user): bool
    {
        $company = $user->currentCompany;

        if ($company === null) {
            return false;
        }

        return filled($company->default_currency)
            && $company->default_payment_terms !== null;
    }

    private function hasPaymentMethod(\App\Models\User $user): bool
    {
        $company = $user->currentCompany;

        if ($company === null) {
            return false;
        }

        return $company->paymentMethods()->exists();
    }
}
