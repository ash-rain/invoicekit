<?php

namespace Tests\Feature;

use App\Livewire\Invoices\CreateInvoice;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PolandKsefIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_invoice_with_polish_seller_persists_ksef_id(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'country' => 'PL']);
        $user->update(['current_company_id' => $company->id]);
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'PL']);

        Livewire::actingAs($user)
            ->test(CreateInvoice::class)
            ->set('clientId', $client->id)
            ->set('issueDate', now()->format('Y-m-d'))
            ->set('dueDate', now()->addDays(30)->format('Y-m-d'))
            ->set('currency', 'EUR')
            ->set('items', [
                ['description' => 'Consulting', 'quantity' => '1', 'unit_price' => '100'],
            ])
            ->set('ksefId', 'KSEF-2026-07-31-000123')
            ->call('save');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'ksef_id' => 'KSEF-2026-07-31-000123',
        ]);
    }

    public function test_creating_invoice_with_non_polish_seller_ignores_ksef_id(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'country' => 'BG']);
        $user->update(['current_company_id' => $company->id]);
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'BG']);

        Livewire::actingAs($user)
            ->test(CreateInvoice::class)
            ->set('clientId', $client->id)
            ->set('issueDate', now()->format('Y-m-d'))
            ->set('dueDate', now()->addDays(30)->format('Y-m-d'))
            ->set('currency', 'EUR')
            ->set('items', [
                ['description' => 'Consulting', 'quantity' => '1', 'unit_price' => '100'],
            ])
            ->set('ksefId', 'KSEF-SHOULD-BE-IGNORED')
            ->call('save');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'ksef_id' => null,
        ]);
    }

    public function test_editing_polish_invoice_hydrates_ksef_id(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'country' => 'PL']);
        $user->update(['current_company_id' => $company->id]);
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'PL']);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'ksef_id' => 'KSEF-EXISTING-000456',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        Livewire::actingAs($user)
            ->test(CreateInvoice::class, ['invoice' => $invoice])
            ->assertSet('ksefId', 'KSEF-EXISTING-000456');
    }

    public function test_pdf_payment_reference_shows_ksef_id_for_polish_company(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'country' => 'PL']);
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'PL']);
        $paymentMethod = PaymentMethod::factory()->bankTransfer()->create(['company_id' => $company->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_method_snapshot' => $paymentMethod->toSnapshot(),
            'ksef_id' => 'KSEF-2026-07-31-000789',
        ]);

        $html = view('invoices.partials.payment-method-pdf', [
            'invoice' => $invoice,
            'company' => $company,
        ])->render();

        $this->assertStringContainsString('KSEF-2026-07-31-000789', $html);
        $this->assertStringContainsString('Payment reference', $html);
    }

    public function test_pdf_payment_reference_hidden_for_non_polish_company_even_if_ksef_id_set(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'country' => 'DE']);
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'DE']);
        $paymentMethod = PaymentMethod::factory()->bankTransfer()->create(['company_id' => $company->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_method_snapshot' => $paymentMethod->toSnapshot(),
            'ksef_id' => 'KSEF-SHOULD-NOT-APPEAR',
        ]);

        $html = view('invoices.partials.payment-method-pdf', [
            'invoice' => $invoice,
            'company' => $company,
        ])->render();

        $this->assertStringNotContainsString('KSEF-SHOULD-NOT-APPEAR', $html);
        $this->assertStringNotContainsString('Payment reference', $html);
    }
}
