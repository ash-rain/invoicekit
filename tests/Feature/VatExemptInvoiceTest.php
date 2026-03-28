<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VatExemptInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function userWithExemptCompany(): User
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'country' => 'DE',
            'vat_exempt' => true,
            'vat_exempt_notice_language' => 'local',
        ]);
        $user->update(['current_company_id' => $company->id]);

        return $user->fresh();
    }

    public function test_vat_exempt_invoice_has_zero_vat(): void
    {
        $user = $this->userWithExemptCompany();
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'FR']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('clientId', $client->id)
            ->set('items', [
                ['description' => 'Consulting', 'quantity' => '2', 'unit_price' => '100.00'],
            ])
            ->assertSet('vatRate', 0.0)
            ->assertSet('vatAmount', 0.0)
            ->assertSet('vatType', 'vat_exempt');
    }

    public function test_vat_exempt_invoice_saves_with_notice(): void
    {
        $user = $this->userWithExemptCompany();
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'FR']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('clientId', $client->id)
            ->set('issueDate', now()->format('Y-m-d'))
            ->set('dueDate', now()->addDays(30)->format('Y-m-d'))
            ->set('items', [
                ['description' => 'Consulting', 'quantity' => '1', 'unit_price' => '500.00'],
            ])
            ->call('save');

        $invoice = Invoice::where('user_id', $user->id)->latest()->first();

        $this->assertNotNull($invoice);
        $this->assertTrue((bool) $invoice->vat_exempt_applied);
        $this->assertNotNull($invoice->vat_exempt_notice);
        $this->assertSame(0.0, (float) $invoice->vat_amount);
        $this->assertSame(500.0, (float) $invoice->total);
    }

    public function test_override_disables_vat_exemption(): void
    {
        $user = $this->userWithExemptCompany();
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'DE']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('clientId', $client->id)
            ->set('vatExemptOverride', true)
            ->set('items', [
                ['description' => 'Service', 'quantity' => '1', 'unit_price' => '100.00'],
            ])
            ->assertSet('vatType', 'standard')
            ->assertSet('vatRate', 19.0);
    }

    public function test_seller_country_auto_populated_from_company(): void
    {
        $user = $this->userWithExemptCompany();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->assertSet('sellerCountry', 'DE');
    }

    public function test_non_exempt_user_has_normal_vat(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'country' => 'DE',
            'vat_exempt' => false,
        ]);
        $user->update(['current_company_id' => $company->id]);
        $user = $user->fresh();

        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'DE']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('clientId', $client->id)
            ->set('items', [
                ['description' => 'Service', 'quantity' => '1', 'unit_price' => '100.00'],
            ])
            ->assertSet('vatType', 'standard')
            ->assertSet('vatRate', 19.0);
    }
}
