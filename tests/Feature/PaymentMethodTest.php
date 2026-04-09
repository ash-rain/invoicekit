<?php

namespace Tests\Feature;

use App\Livewire\Settings\PaymentMethods;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────
    // Model & relationships
    // ──────────────────────────────────────────────────────────────────

    public function test_payment_method_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $method = PaymentMethod::factory()->create(['company_id' => $company->id]);

        $this->assertTrue($method->company->is($company));
    }

    public function test_company_has_many_payment_methods(): void
    {
        $company = Company::factory()->create();
        PaymentMethod::factory()->count(3)->create(['company_id' => $company->id]);

        $this->assertCount(3, $company->paymentMethods);
    }

    public function test_company_default_payment_method_returns_correct_method(): void
    {
        $company = Company::factory()->create();
        PaymentMethod::factory()->create(['company_id' => $company->id, 'is_default' => false]);
        $default = PaymentMethod::factory()->default()->create(['company_id' => $company->id]);

        $this->assertTrue($company->defaultPaymentMethod->is($default));
    }

    public function test_display_label_returns_label_when_set(): void
    {
        $method = PaymentMethod::factory()->create(['label' => 'My Account']);
        $this->assertEquals('My Account', $method->displayLabel());
    }

    public function test_display_label_falls_back_to_bank_name(): void
    {
        $method = PaymentMethod::factory()->create([
            'label' => null,
            'type' => PaymentMethod::TYPE_BANK_TRANSFER,
            'bank_name' => 'Test Bank',
        ]);
        $this->assertEquals('Test Bank', $method->displayLabel());
    }

    public function test_to_snapshot_returns_expected_keys(): void
    {
        $method = PaymentMethod::factory()->create([
            'type' => PaymentMethod::TYPE_BANK_TRANSFER,
            'bank_iban' => 'DE89370400440532013000',
            'bank_bic' => 'COBADEFFXXX',
        ]);

        $snapshot = $method->toSnapshot();

        $this->assertEquals($method->id, $snapshot['id']);
        $this->assertEquals('bank_transfer', $snapshot['type']);
        $this->assertEquals('DE89370400440532013000', $snapshot['bank_iban']);
        $this->assertEquals('COBADEFFXXX', $snapshot['bank_bic']);
    }

    // ──────────────────────────────────────────────────────────────────
    // Plan limits
    // ──────────────────────────────────────────────────────────────────

    public function test_free_plan_allows_one_payment_method(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        $planService = new PlanService;
        $this->assertTrue($planService->canAddPaymentMethod($user));

        PaymentMethod::factory()->create(['company_id' => $company->id]);
        $this->assertFalse($planService->canAddPaymentMethod($user->fresh()));
    }

    public function test_starter_plan_allows_three_payment_methods(): void
    {
        $user = User::factory()->create(['plan' => 'starter']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        PaymentMethod::factory()->count(2)->create(['company_id' => $company->id]);
        $planService = new PlanService;
        $this->assertTrue($planService->canAddPaymentMethod($user->fresh()));

        PaymentMethod::factory()->create(['company_id' => $company->id]);
        $this->assertFalse($planService->canAddPaymentMethod($user->fresh()));
    }

    public function test_pro_plan_allows_unlimited_payment_methods(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        PaymentMethod::factory()->count(10)->create(['company_id' => $company->id]);

        $planService = new PlanService;
        $this->assertTrue($planService->canAddPaymentMethod($user->fresh()));
        $this->assertNull($planService->paymentMethodsRemaining($user->fresh()));
    }

    // ──────────────────────────────────────────────────────────────────
    // Livewire CRUD
    // ──────────────────────────────────────────────────────────────────

    public function test_can_add_bank_transfer_payment_method(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(PaymentMethods::class)
            ->call('add')
            ->set('type', 'bank_transfer')
            ->set('label', 'Main Account')
            ->set('bankName', 'Test Bank')
            ->set('bankIban', 'DE89370400440532013000')
            ->set('bankBic', 'COBADEFFXXX')
            ->call('save');

        $this->assertDatabaseHas('payment_methods', [
            'company_id' => $company->id,
            'type' => 'bank_transfer',
            'label' => 'Main Account',
            'bank_iban' => 'DE89370400440532013000',
            'is_default' => true, // First method becomes default
        ]);
    }

    public function test_can_add_cash_payment_method(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(PaymentMethods::class)
            ->call('add')
            ->set('type', 'cash')
            ->set('label', 'Cash Payment')
            ->set('notes', 'Pay at office')
            ->call('save');

        $this->assertDatabaseHas('payment_methods', [
            'company_id' => $company->id,
            'type' => 'cash',
            'label' => 'Cash Payment',
            'notes' => 'Pay at office',
        ]);
    }

    public function test_can_edit_payment_method(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        $method = PaymentMethod::factory()->bankTransfer()->create([
            'company_id' => $company->id,
            'label' => 'Old Label',
        ]);

        Livewire::actingAs($user)
            ->test(PaymentMethods::class)
            ->call('edit', $method->id)
            ->set('label', 'New Label')
            ->call('save');

        $this->assertEquals('New Label', $method->fresh()->label);
    }

    public function test_can_delete_payment_method(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        $method = PaymentMethod::factory()->create(['company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(PaymentMethods::class)
            ->call('confirmDelete', $method->id)
            ->call('delete');

        $this->assertDatabaseMissing('payment_methods', ['id' => $method->id]);
    }

    public function test_deleting_default_promotes_next_method(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        $default = PaymentMethod::factory()->default()->create(['company_id' => $company->id]);
        $other = PaymentMethod::factory()->create(['company_id' => $company->id, 'is_default' => false]);

        Livewire::actingAs($user)
            ->test(PaymentMethods::class)
            ->call('confirmDelete', $default->id)
            ->call('delete');

        $this->assertTrue($other->fresh()->is_default);
    }

    public function test_can_set_default_payment_method(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        $method1 = PaymentMethod::factory()->default()->create(['company_id' => $company->id]);
        $method2 = PaymentMethod::factory()->create(['company_id' => $company->id, 'is_default' => false]);

        Livewire::actingAs($user)
            ->test(PaymentMethods::class)
            ->call('setDefault', $method2->id);

        $this->assertFalse($method1->fresh()->is_default);
        $this->assertTrue($method2->fresh()->is_default);
    }

    public function test_plan_limit_prevents_adding_payment_method(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        PaymentMethod::factory()->create(['company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(PaymentMethods::class)
            ->call('add')
            ->assertSet('showForm', false);

        // Verify no new method was added
        $this->assertCount(1, $company->paymentMethods);
    }

    public function test_stripe_type_cannot_be_edited(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        $stripe = PaymentMethod::factory()->stripe()->create(['company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(PaymentMethods::class)
            ->call('edit', $stripe->id)
            ->assertSet('showForm', false);
    }

    public function test_bank_transfer_requires_iban(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(PaymentMethods::class)
            ->call('add')
            ->set('type', 'bank_transfer')
            ->set('bankIban', '')
            ->call('save')
            ->assertHasErrors('bankIban');
    }

    // ──────────────────────────────────────────────────────────────────
    // Invoice integration
    // ──────────────────────────────────────────────────────────────────

    public function test_invoice_resolved_payment_method_returns_snapshot(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        $snapshot = [
            'id' => 1,
            'type' => 'bank_transfer',
            'label' => 'Snapshot Bank',
            'bank_iban' => 'DE89370400440532013000',
            'bank_bic' => 'COBADEFFXXX',
            'bank_name' => 'Snapshot Bank Name',
            'notes' => null,
        ];

        $invoice = \App\Models\Invoice::factory()->create([
            'user_id' => $user->id,
            'payment_method_snapshot' => $snapshot,
        ]);

        $resolved = $invoice->resolvedPaymentMethod();
        $this->assertEquals('Snapshot Bank', $resolved['label']);
        $this->assertEquals('DE89370400440532013000', $resolved['bank_iban']);
    }

    public function test_invoice_resolved_payment_method_falls_back_to_live_method(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        $method = PaymentMethod::factory()->create([
            'company_id' => $company->id,
            'bank_iban' => 'FR7630006000011234567890189',
        ]);

        $invoice = \App\Models\Invoice::factory()->create([
            'user_id' => $user->id,
            'payment_method_id' => $method->id,
            'payment_method_snapshot' => null,
        ]);

        $resolved = $invoice->resolvedPaymentMethod();
        $this->assertEquals('FR7630006000011234567890189', $resolved['bank_iban']);
    }
}
