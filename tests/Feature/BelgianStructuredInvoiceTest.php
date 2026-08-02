<?php

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceList;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\InvoiceValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BelgianStructuredInvoiceTest extends TestCase
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
            'country' => 'BE',
        ]);
        $this->user->update(['current_company_id' => $this->company->id]);
    }

    private function makeInvoice(array $invoiceAttributes = [], array $clientAttributes = []): Invoice
    {
        $client = Client::factory()->create(array_merge([
            'user_id' => $this->user->id,
            'country' => 'BE',
            'vat_number' => 'BE0123456789',
        ], $clientAttributes));

        $invoice = Invoice::factory()->create(array_merge([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'document_type' => 'invoice',
            'status' => 'draft',
        ], $invoiceAttributes));

        InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'unit' => 'pcs']);

        return $invoice->fresh(['items', 'client']);
    }

    public function test_can_issue_false_for_be_b2b_invoice_without_xml_export(): void
    {
        $invoice = $this->makeInvoice(['xml_exported_at' => null]);

        $service = new InvoiceValidationService;
        $result = $service->validate($invoice, $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('xml_export'));
        $this->assertFalse($service->canIssue($invoice, $this->company));
    }

    public function test_can_issue_true_for_be_b2b_invoice_with_xml_export(): void
    {
        $invoice = $this->makeInvoice(['xml_exported_at' => now()]);

        $service = new InvoiceValidationService;
        $result = $service->validate($invoice, $this->company);

        $this->assertEmpty($result->errorsForField('xml_export'));
        $this->assertTrue($service->canIssue($invoice, $this->company));
    }

    public function test_no_xml_export_error_for_non_belgian_client(): void
    {
        $invoice = $this->makeInvoice(
            ['xml_exported_at' => null],
            ['country' => 'DE', 'vat_number' => 'DE123456789']
        );

        $service = new InvoiceValidationService;
        $result = $service->validate($invoice, $this->company);

        $this->assertEmpty($result->errorsForField('xml_export'));
    }

    public function test_no_xml_export_error_for_belgian_consumer_without_vat_number(): void
    {
        $invoice = $this->makeInvoice(
            ['xml_exported_at' => null],
            ['country' => 'BE', 'vat_number' => null]
        );

        $service = new InvoiceValidationService;
        $result = $service->validate($invoice, $this->company);

        $this->assertEmpty($result->errorsForField('xml_export'));
    }

    public function test_xml_download_route_sets_xml_exported_at(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BE',
            'vat_number' => 'BE0123456789',
        ]);
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'document_type' => 'invoice',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->assertNull($invoice->xml_exported_at);

        $this->actingAs($this->user)
            ->get(route('invoices.xml', $invoice))
            ->assertOk();

        $this->assertNotNull($invoice->fresh()->xml_exported_at);
    }

    public function test_mark_sent_blocked_then_allowed_after_xml_export_for_belgian_b2b_invoice(): void
    {
        $this->actingAs($this->user);

        $invoice = $this->makeInvoice(['xml_exported_at' => null]);

        Livewire::test(InvoiceList::class)
            ->call('markSent', $invoice->id);

        $invoice->refresh();
        $this->assertSame('draft', $invoice->status);

        // Generate the structured export, which should unblock issuance.
        $this->get(route('invoices.xml', $invoice))->assertOk();

        Livewire::test(InvoiceList::class)
            ->call('markSent', $invoice->id);

        $invoice->refresh();
        $this->assertSame('sent', $invoice->status);
    }
}
