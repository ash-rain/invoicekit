<?php

namespace Tests\Feature;

use App\Livewire\SetupGuide;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SetupGuideTest extends TestCase
{
    use RefreshDatabase;

    // ─── Visibility ──────────────────────────────────────────────────────────

    public function test_setup_guide_is_visible_for_new_user_with_no_company(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SetupGuide::class)
            ->assertSee(__('Setup Guide'));
    }

    public function test_setup_guide_is_hidden_after_user_dismisses_it(): void
    {
        $user = User::factory()->create([
            'setup_guide_dismissed_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(SetupGuide::class)
            ->assertDontSee(__('Setup Guide'));
    }

    public function test_setup_guide_is_hidden_when_all_steps_are_completed(): void
    {
        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_test',
            'stripe_connect_onboarded' => true,
            'gemini_api_key' => 'test-gemini-key',
            'setup_guide_dismissed_steps' => ['import_invoices', 'import_expenses'],
        ]);

        $company = Company::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Co',
            'address_line1' => '123 Main St',
            'country' => 'DE',
            'default_currency' => 'EUR',
            'default_payment_terms' => 30,
        ]);

        \App\Models\PaymentMethod::factory()->create([
            'company_id' => $company->id,
            'is_default' => true,
        ]);

        $user->update(['current_company_id' => $company->id]);

        Livewire::actingAs($user->fresh())
            ->test(SetupGuide::class)
            ->assertDontSee(__('Setup Guide'));
    }

    // ─── Step 1: Business Profile (auto-detect) ───────────────────────────────

    public function test_business_profile_step_is_incomplete_with_no_company(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(SetupGuide::class);

        $this->assertFalse($component->instance()->isStepCompleted('business_profile'));
    }

    public function test_business_profile_step_auto_completes_when_fields_are_filled(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'user_id' => $user->id,
            'name' => 'My Company',
            'address_line1' => '123 Street',
            'country' => 'DE',
        ]);

        $user->update(['current_company_id' => $company->id]);

        $component = Livewire::actingAs($user->fresh())->test(SetupGuide::class);

        $this->assertTrue($component->instance()->isStepCompleted('business_profile'));
    }

    public function test_business_profile_step_is_incomplete_when_required_fields_missing(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'user_id' => $user->id,
            'name' => 'My Company',
            'address_line1' => null,
            'country' => null,
        ]);

        $user->update(['current_company_id' => $company->id]);

        $component = Livewire::actingAs($user->fresh())->test(SetupGuide::class);

        $this->assertFalse($component->instance()->isStepCompleted('business_profile'));
    }

    // ─── Step 2: Invoicing Defaults (auto-detect) ─────────────────────────────

    public function test_invoicing_defaults_step_auto_completes_with_currency_and_terms(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'user_id' => $user->id,
            'default_currency' => 'EUR',
            'default_payment_terms' => 30,
        ]);

        $user->update(['current_company_id' => $company->id]);

        $component = Livewire::actingAs($user->fresh())->test(SetupGuide::class);

        $this->assertTrue($component->instance()->isStepCompleted('invoicing_defaults'));
    }

    public function test_invoicing_defaults_step_is_incomplete_without_company(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(SetupGuide::class);

        $this->assertFalse($component->instance()->isStepCompleted('invoicing_defaults'));
    }

    // ─── Step 3: Stripe (auto-detect + dismissible) ───────────────────────────

    public function test_stripe_step_auto_completes_when_stripe_connect_onboarded(): void
    {
        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_123',
            'stripe_connect_onboarded' => true,
        ]);

        $component = Livewire::actingAs($user)->test(SetupGuide::class);

        $this->assertTrue($component->instance()->isStepCompleted('connect_stripe'));
    }

    public function test_stripe_step_is_incomplete_when_not_onboarded(): void
    {
        $user = User::factory()->create([
            'stripe_connect_onboarded' => false,
        ]);

        $component = Livewire::actingAs($user)->test(SetupGuide::class);

        $this->assertFalse($component->instance()->isStepCompleted('connect_stripe'));
    }

    public function test_stripe_step_can_be_manually_dismissed(): void
    {
        $user = User::factory()->create([
            'stripe_connect_onboarded' => false,
        ]);

        Livewire::actingAs($user)
            ->test(SetupGuide::class)
            ->call('dismissStep', 'connect_stripe');

        $this->assertContains('connect_stripe', $user->fresh()->setup_guide_dismissed_steps);
    }

    // ─── Step 4: AI API Key (auto-detect + dismissible) ───────────────────────

    public function test_ai_step_auto_completes_when_gemini_key_is_set(): void
    {
        $user = User::factory()->create([
            'gemini_api_key' => 'test-key-abc',
        ]);

        $component = Livewire::actingAs($user)->test(SetupGuide::class);

        $this->assertTrue($component->instance()->isStepCompleted('ai_services'));
    }

    public function test_ai_step_is_incomplete_when_no_gemini_key(): void
    {
        $user = User::factory()->create(['gemini_api_key' => null]);

        $component = Livewire::actingAs($user)->test(SetupGuide::class);

        $this->assertFalse($component->instance()->isStepCompleted('ai_services'));
    }

    public function test_ai_step_can_be_manually_dismissed(): void
    {
        $user = User::factory()->create(['gemini_api_key' => null]);

        Livewire::actingAs($user)
            ->test(SetupGuide::class)
            ->call('dismissStep', 'ai_services');

        $this->assertContains('ai_services', $user->fresh()->setup_guide_dismissed_steps);
    }

    // ─── Steps 5 & 6: Import steps (dismiss-only) ─────────────────────────────

    public function test_import_invoices_step_is_incomplete_by_default(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(SetupGuide::class);

        $this->assertFalse($component->instance()->isStepCompleted('import_invoices'));
    }

    public function test_import_invoices_step_can_be_dismissed(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SetupGuide::class)
            ->call('dismissStep', 'import_invoices');

        $this->assertContains('import_invoices', $user->fresh()->setup_guide_dismissed_steps);
    }

    public function test_import_expenses_step_can_be_dismissed(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SetupGuide::class)
            ->call('dismissStep', 'import_expenses');

        $this->assertContains('import_expenses', $user->fresh()->setup_guide_dismissed_steps);
    }

    // ─── Non-dismissible steps cannot be dismissed ───────────────────────────

    public function test_business_profile_step_cannot_be_manually_dismissed(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SetupGuide::class)
            ->call('dismissStep', 'business_profile');

        $this->assertNotContains('business_profile', $user->fresh()->setup_guide_dismissed_steps ?? []);
    }

    public function test_invoicing_defaults_step_cannot_be_manually_dismissed(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SetupGuide::class)
            ->call('dismissStep', 'invoicing_defaults');

        $this->assertNotContains('invoicing_defaults', $user->fresh()->setup_guide_dismissed_steps ?? []);
    }

    // ─── Dismissed steps persist and count as completed ──────────────────────

    public function test_dismissed_step_is_counted_as_completed(): void
    {
        $user = User::factory()->create([
            'setup_guide_dismissed_steps' => ['connect_stripe'],
        ]);

        $component = Livewire::actingAs($user)->test(SetupGuide::class);

        $this->assertTrue($component->instance()->isStepCompleted('connect_stripe'));
    }

    // ─── Progress calculation ─────────────────────────────────────────────────

    public function test_progress_is_zero_for_new_user(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(SetupGuide::class);

        $this->assertSame(0, $component->instance()->progressPercent());
    }

    public function test_progress_is_100_when_all_steps_are_completed(): void
    {
        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_test',
            'stripe_connect_onboarded' => true,
            'gemini_api_key' => 'test-key',
            'setup_guide_dismissed_steps' => ['import_invoices', 'import_expenses'],
        ]);

        $company = Company::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Co',
            'address_line1' => '1 Main St',
            'country' => 'DE',
            'default_currency' => 'EUR',
            'default_payment_terms' => 30,
        ]);

        \App\Models\PaymentMethod::factory()->create([
            'company_id' => $company->id,
            'is_default' => true,
        ]);

        $user->update(['current_company_id' => $company->id]);

        $component = Livewire::actingAs($user->fresh())->test(SetupGuide::class);

        $this->assertSame(100, $component->instance()->progressPercent());
    }

    public function test_progress_increases_as_steps_are_completed(): void
    {
        $user = User::factory()->create([
            'setup_guide_dismissed_steps' => ['import_invoices', 'import_expenses'],
        ]);

        $component = Livewire::actingAs($user)->test(SetupGuide::class);

        // 2 out of 7 steps = 29% (round(2/7*100))
        $this->assertSame(29, $component->instance()->progressPercent());
    }

    // ─── Dismiss entire guide ─────────────────────────────────────────────────

    public function test_dismissing_guide_sets_dismissed_at_timestamp(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SetupGuide::class)
            ->call('dismissGuide');

        $this->assertNotNull($user->fresh()->setup_guide_dismissed_at);
    }

    // ─── Step: Payment Method (auto-detect + dismissible) ─────────────────────

    public function test_payment_method_step_is_incomplete_when_no_payment_method_configured(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'user_id' => $user->id,
        ]);

        $user->update(['current_company_id' => $company->id]);

        $component = Livewire::actingAs($user->fresh())->test(SetupGuide::class);

        $this->assertFalse($component->instance()->isStepCompleted('payment_method'));
    }

    public function test_payment_method_step_auto_completes_when_payment_method_exists(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'user_id' => $user->id,
        ]);

        \App\Models\PaymentMethod::factory()->create([
            'company_id' => $company->id,
            'type' => 'bank_transfer',
            'is_default' => true,
        ]);

        $user->update(['current_company_id' => $company->id]);

        $component = Livewire::actingAs($user->fresh())->test(SetupGuide::class);

        $this->assertTrue($component->instance()->isStepCompleted('payment_method'));
    }

    public function test_payment_method_step_can_be_dismissed(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SetupGuide::class)
            ->call('dismissStep', 'payment_method');

        $this->assertContains('payment_method', $user->fresh()->setup_guide_dismissed_steps);
    }

    // ─── Step definitions integrity ───────────────────────────────────────────

    public function test_step_definitions_have_required_keys(): void
    {
        $requiredKeys = ['key', 'title', 'description', 'url', 'cta', 'auto_detect', 'dismissible'];

        foreach (SetupGuide::steps() as $step) {
            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $step, "Step is missing key: {$key}");
            }
        }
    }

    public function test_step_keys_are_unique(): void
    {
        $keys = array_column(SetupGuide::steps(), 'key');

        $this->assertSame(count($keys), count(array_unique($keys)), 'Duplicate step keys found');
    }
}
