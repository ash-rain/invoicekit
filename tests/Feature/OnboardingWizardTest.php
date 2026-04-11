<?php

namespace Tests\Feature;

use App\Livewire\OnboardingWizard;
use App\Models\Client;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OnboardingWizardTest extends TestCase
{
    use RefreshDatabase;

    // ─── Redirect if already onboarded ───────────────────────────────────────

    public function test_completed_user_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create(['onboarding_completed' => true]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->assertRedirect(route('dashboard'));
    }

    // ─── Step 1: Business info ────────────────────────────────────────────────

    public function test_step_1_is_shown_on_mount(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->assertSet('step', 1);
    }

    public function test_step_1_requires_company_name(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', '')
            ->call('nextStep')
            ->assertHasErrors(['companyName' => 'required']);
    }

    public function test_step_1_requires_company_country(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyCountry', '')
            ->call('nextStep')
            ->assertHasErrors(['companyCountry']);
    }

    public function test_step_1_advances_to_step_2_when_valid(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'BG')
            ->call('nextStep')
            ->assertSet('step', 2);
    }

    // ─── Step 2: VAT & Tax ────────────────────────────────────────────────────

    public function test_step_2_requires_vat_number_for_eu_countries(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'DE')
            ->call('nextStep') // advance to step 2
            ->set('vatNumber', '')
            ->call('nextStep')
            ->assertHasErrors(['vatNumber']);
    }

    public function test_step_2_vat_number_is_optional_for_non_eu_countries(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'US')
            ->call('nextStep') // advance to step 2
            ->set('vatNumber', '')
            ->call('nextStep')
            ->assertSet('step', 3); // advances without error
    }

    public function test_step_2_advances_to_step_3_when_valid(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'BG')
            ->call('nextStep')
            ->set('vatNumber', 'BG123456789')
            ->call('nextStep')
            ->assertSet('step', 3);
    }

    public function test_vat_exempt_toggle_can_be_set_on_step_2(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyCountry', 'BG')
            ->set('vatExempt', true)
            ->assertSet('vatExempt', true);
    }

    // ─── Step 3: First Client ─────────────────────────────────────────────────

    public function test_step_3_requires_client_name(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyCountry', 'BG')
            ->set('step', 3)
            ->set('clientName', '')
            ->call('nextStep')
            ->assertHasErrors(['clientName' => 'required']);
    }

    public function test_client_country_defaults_to_company_country(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        $component = Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyCountry', 'BG');

        $this->assertSame('BG', $component->get('clientCountry'));
    }

    public function test_step_3_advances_to_step_4_when_valid(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'US')
            ->call('nextStep') // step 1 -> 2
            ->call('nextStep') // step 2 -> 3 (US, no VAT required)
            ->set('clientName', 'Acme Corp')
            ->set('clientCountry', 'US')
            ->call('nextStep')
            ->assertSet('step', 4);
    }

    // ─── Step 4: First Project (skippable) ───────────────────────────────────

    public function test_step_4_can_be_skipped(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('step', 4)
            ->set('skipProject', true)
            ->call('nextStep')
            ->assertSet('step', 5);
    }

    public function test_step_4_advances_when_project_name_provided(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('step', 4)
            ->set('skipProject', false)
            ->set('projectName', 'Website Redesign')
            ->set('hourlyRate', '75')
            ->call('nextStep')
            ->assertSet('step', 5);
    }

    // ─── Step 5: Payment Method (skippable) ──────────────────────────────────

    public function test_step_5_can_be_skipped(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('step', 5)
            ->set('skipPayment', true)
            ->call('nextStep')
            ->assertSet('step', 6);
    }

    public function test_step_5_requires_iban_when_bank_transfer_selected(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('step', 5)
            ->set('skipPayment', false)
            ->set('paymentMethodType', 'bank_transfer')
            ->set('bankIban', '')
            ->call('nextStep')
            ->assertHasErrors(['bankIban']);
    }

    public function test_step_5_advances_when_bank_transfer_with_iban(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('step', 5)
            ->set('skipPayment', false)
            ->set('paymentMethodType', 'bank_transfer')
            ->set('bankIban', 'BG80BNBG96611020345678')
            ->call('nextStep')
            ->assertSet('step', 6);
    }

    // ─── complete() transaction ───────────────────────────────────────────────

    public function test_complete_creates_company_with_vat_number(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'BG')
            ->set('vatNumber', 'BG123456789')
            ->set('registrationNumber', '123456789')
            ->set('vatExempt', false)
            ->set('clientName', 'Acme GmbH')
            ->set('clientCountry', 'BG')
            ->set('clientCurrency', 'BGN')
            ->set('skipProject', true)
            ->set('skipPayment', true)
            ->set('step', 6)
            ->call('complete')
            ->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->onboarding_completed);

        $company = $user->fresh()->currentCompany;
        $this->assertNotNull($company);
        $this->assertSame('BG123456789', $company->vat_number);
        $this->assertSame('123456789', $company->registration_number);
    }

    public function test_complete_creates_client(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'US')
            ->set('clientName', 'Acme Inc')
            ->set('clientCountry', 'US')
            ->set('clientCurrency', 'USD')
            ->set('skipProject', true)
            ->set('skipPayment', true)
            ->set('step', 6)
            ->call('complete');

        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'Acme Inc',
            'country' => 'US',
            'currency' => 'USD',
        ]);
    }

    public function test_complete_creates_project_when_not_skipped(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'US')
            ->set('clientName', 'Acme Inc')
            ->set('clientCountry', 'US')
            ->set('clientCurrency', 'USD')
            ->set('skipProject', false)
            ->set('projectName', 'Website Redesign')
            ->set('hourlyRate', '75')
            ->set('skipPayment', true)
            ->set('step', 6)
            ->call('complete');

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'name' => 'Website Redesign',
        ]);
    }

    public function test_complete_skips_project_when_flag_set(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'US')
            ->set('clientName', 'Acme Inc')
            ->set('clientCountry', 'US')
            ->set('clientCurrency', 'USD')
            ->set('skipProject', true)
            ->set('skipPayment', true)
            ->set('step', 6)
            ->call('complete');

        $this->assertDatabaseMissing('projects', ['user_id' => $user->id]);
    }

    public function test_complete_creates_payment_method_when_not_skipped(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'US')
            ->set('clientName', 'Acme Inc')
            ->set('clientCountry', 'US')
            ->set('clientCurrency', 'USD')
            ->set('skipProject', true)
            ->set('skipPayment', false)
            ->set('paymentMethodType', 'bank_transfer')
            ->set('bankIban', 'DE89370400440532013000')
            ->set('step', 6)
            ->call('complete');

        $company = $user->fresh()->currentCompany;
        $this->assertNotNull($company->defaultPaymentMethod);
        $this->assertSame('bank_transfer', $company->defaultPaymentMethod->type);
        $this->assertSame('DE89370400440532013000', $company->defaultPaymentMethod->bank_iban);
    }

    public function test_complete_skips_payment_method_when_flag_set(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'US')
            ->set('clientName', 'Acme Inc')
            ->set('clientCountry', 'US')
            ->set('clientCurrency', 'USD')
            ->set('skipProject', true)
            ->set('skipPayment', true)
            ->set('step', 6)
            ->call('complete');

        $company = $user->fresh()->currentCompany;
        $this->assertNull($company?->defaultPaymentMethod);
    }

    public function test_complete_sets_vat_exempt_on_company(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'BG')
            ->set('vatNumber', 'BG123456789')
            ->set('vatExempt', true)
            ->set('clientName', 'Acme GmbH')
            ->set('clientCountry', 'BG')
            ->set('clientCurrency', 'BGN')
            ->set('skipProject', true)
            ->set('skipPayment', true)
            ->set('step', 6)
            ->call('complete');

        $this->assertTrue($user->fresh()->currentCompany->vat_exempt);
    }

    public function test_complete_marks_onboarding_as_completed(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->set('companyName', 'Test Co')
            ->set('companyCountry', 'US')
            ->set('clientName', 'Acme Inc')
            ->set('clientCountry', 'US')
            ->set('clientCurrency', 'USD')
            ->set('skipProject', true)
            ->set('skipPayment', true)
            ->set('step', 6)
            ->call('complete');

        $this->assertTrue($user->fresh()->onboarding_completed);
    }
}
