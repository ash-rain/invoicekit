<?php

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceList;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceValidationGateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'registration_number' => '123456789',
            'address_line1' => 'ул. Тестова 1',
        ]);
        $this->user->update(['current_company_id' => $this->company->id]);
    }

    public function test_can_issue_false_for_incomplete_bg_invoice(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'registration_number' => '987654321',
            'address' => 'ул. Клиентска 1',
        ]);

        $paymentMethod = PaymentMethod::factory()->create(['company_id' => $this->company->id]);

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'invoice_number' => '0000000001',
            'issue_date' => '2026-04-11',
            'tax_event_date' => null, // Missing - will fail BG validation
            'issued_by_name' => 'Иван Иванов',
            'received_by_name' => 'Петър Петров',
            'payment_due_date' => '2026-05-11',
            'document_type' => 'invoice',
        ]);

        InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'vat_rate_key' => 'standard']);

        $service = new \App\Services\InvoiceValidationService;
        $result = $service->validate($invoice->fresh(['items', 'client']), $this->company);

        $this->assertTrue($result->fails(), 'Invoice with missing tax_event_date should fail. Errors: '.json_encode($result->errors()));
        $this->assertFalse($service->canIssue($invoice->fresh(['items', 'client']), $this->company));
    }

    public function test_can_issue_true_for_complete_bg_invoice(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'registration_number' => '987654321',
            'address' => 'ул. Клиентска 1',
        ]);

        $paymentMethod = PaymentMethod::factory()->create(['company_id' => $this->company->id]);

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'invoice_number' => '0000000001',
            'issue_date' => '2026-04-11',
            'due_date' => '2026-05-11',
            'payment_due_date' => '2026-05-11',
            'tax_event_date' => '2026-04-11',
            'issued_by_name' => 'Иван Иванов',
            'received_by_name' => 'Петър Петров',
            'document_type' => 'invoice',
            'currency' => 'BGN',
            'payment_method_id' => $paymentMethod->id,
            'payment_method_snapshot' => $paymentMethod->toSnapshot(),
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Услуга',
            'unit' => 'бр.',
            'quantity' => 1,
            'unit_price' => 100,
            'vat_rate' => 20,
            'vat_rate_key' => 'standard',
            'total' => 100,
        ]);

        $service = new \App\Services\InvoiceValidationService;
        $this->assertTrue($service->canIssue($invoice->fresh(['items', 'client']), $this->company));
    }

    public function test_mark_sent_blocked_by_validation_gate_in_invoice_list(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'registration_number' => '987654321',
            'address' => 'ул. Клиентска 1',
        ]);

        // Invoice missing required BG fields
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'status' => 'draft',
            'document_type' => 'invoice',
            'tax_event_date' => null,
            'issued_by_name' => null,
        ]);

        Livewire::test(InvoiceList::class)
            ->call('markSent', $invoice->id);

        // Invoice should still be draft because validation failed
        $invoice->refresh();
        $this->assertSame('draft', $invoice->status);
    }
}
