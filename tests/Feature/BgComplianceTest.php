<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\UblXmlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BgComplianceTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────
    // BG sequential numbering
    // ──────────────────────────────────────────────────────────────────

    public function test_bg_sequential_number_is_10_digit_zero_padded(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'invoice_numbering_format' => 'bg_sequential',
            'bg_invoice_sequence_start' => 1,
        ]);

        $number = Invoice::generateNumber($user->id, $company);

        $this->assertSame('0000000001', $number);
    }

    public function test_bg_sequential_number_starts_from_configured_value(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'invoice_numbering_format' => 'bg_sequential',
            'bg_invoice_sequence_start' => 500,
        ]);

        $number = Invoice::generateNumber($user->id, $company);

        $this->assertSame('0000000500', $number);
    }

    public function test_bg_sequential_number_increments_from_last_invoice(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'invoice_numbering_format' => 'bg_sequential',
            'bg_invoice_sequence_start' => 1,
        ]);

        Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => '0000000042',
            'document_type' => 'invoice',
        ]);

        $number = Invoice::generateNumber($user->id, $company);

        $this->assertSame('0000000043', $number);
    }

    public function test_bg_sequence_is_shared_across_document_types(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'invoice_numbering_format' => 'bg_sequential',
            'bg_invoice_sequence_start' => 1,
        ]);

        Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => '0000000010',
            'document_type' => 'credit_note',
        ]);

        $number = Invoice::generateNumber($user->id, $company);

        $this->assertSame('0000000011', $number);
    }

    public function test_proforma_does_not_count_toward_bg_sequence(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'invoice_numbering_format' => 'bg_sequential',
            'bg_invoice_sequence_start' => 1,
        ]);

        Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => '0000000099',
            'document_type' => 'proforma',
        ]);

        $number = Invoice::generateNumber($user->id, $company);

        $this->assertSame('0000000001', $number);
    }

    public function test_standard_format_used_when_company_has_standard_setting(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'invoice_numbering_format' => 'standard',
            'invoice_prefix' => 'INV',
            'invoice_starting_number' => 1,
        ]);

        $number = Invoice::generateNumber($user->id, $company);

        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{4}$/', $number);
    }

    // ──────────────────────────────────────────────────────────────────
    // Invoice cancellation
    // ──────────────────────────────────────────────────────────────────

    public function test_cancel_invoice_sets_cancelled_status_and_timestamp(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'sent',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->call('cancelInvoice', $invoice->id);

        $invoice->refresh();
        $this->assertSame('cancelled', $invoice->status);
        $this->assertNotNull($invoice->cancelled_at);
        $this->assertNull($invoice->cancellation_reason);
    }

    public function test_cancel_invoice_stores_cancellation_reason(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'sent',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->call('cancelInvoice', $invoice->id, 'Duplicate invoice');

        $invoice->refresh();
        $this->assertSame('cancelled', $invoice->status);
        $this->assertSame('Duplicate invoice', $invoice->cancellation_reason);
    }

    public function test_cannot_cancel_another_users_invoice(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $other->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $other->id,
            'client_id' => $client->id,
            'status' => 'sent',
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->call('cancelInvoice', $invoice->id);
    }

    // ──────────────────────────────────────────────────────────────────
    // Credit notes
    // ──────────────────────────────────────────────────────────────────

    public function test_credit_note_references_original_invoice(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $original = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'document_type' => 'invoice',
        ]);
        $creditNote = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'document_type' => 'credit_note',
            'original_invoice_id' => $original->id,
        ]);

        $this->assertSame('credit_note', $creditNote->document_type);
        $this->assertSame($original->id, $creditNote->original_invoice_id);
    }

    public function test_original_invoice_has_credit_notes_relationship(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $original = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'document_type' => 'invoice',
        ]);
        Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'document_type' => 'credit_note',
            'original_invoice_id' => $original->id,
        ]);

        $this->assertCount(1, $original->creditNotes);
    }

    // ──────────────────────────────────────────────────────────────────
    // UBL type codes
    // ──────────────────────────────────────────────────────────────────

    public function test_ubl_invoice_has_type_code_380(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'document_type' => 'invoice',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $xml = (new UblXmlService)->generate($invoice->load(['client', 'items']));

        $this->assertStringContainsString('<cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>', $xml);
    }

    public function test_ubl_credit_note_has_type_code_381(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'document_type' => 'credit_note',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $xml = (new UblXmlService)->generate($invoice->load(['client', 'items']));

        $this->assertStringContainsString('<cbc:InvoiceTypeCode>381</cbc:InvoiceTypeCode>', $xml);
    }

    public function test_ubl_debit_note_has_type_code_383(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'document_type' => 'debit_note',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $xml = (new UblXmlService)->generate($invoice->load(['client', 'items']));

        $this->assertStringContainsString('<cbc:InvoiceTypeCode>383</cbc:InvoiceTypeCode>', $xml);
    }

    public function test_ubl_credit_note_includes_billing_reference_to_original(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $original = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2025-0001',
            'document_type' => 'invoice',
        ]);
        $creditNote = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'document_type' => 'credit_note',
            'original_invoice_id' => $original->id,
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $creditNote->id]);

        $xml = (new UblXmlService)->generate(
            $creditNote->load(['client', 'items', 'originalInvoice'])
        );

        $this->assertStringContainsString('INV-2025-0001', $xml);
    }

    // ──────────────────────────────────────────────────────────────────
    // Tax event date
    // ──────────────────────────────────────────────────────────────────

    public function test_ubl_includes_tax_event_date_when_set(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'tax_event_date' => '2025-06-15',
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $xml = (new UblXmlService)->generate($invoice->load(['client', 'items']));

        $this->assertStringContainsString('<cbc:TaxPointDate>2025-06-15</cbc:TaxPointDate>', $xml);
    }

    // ──────────────────────────────────────────────────────────────────
    // Document type filter
    // ──────────────────────────────────────────────────────────────────

    public function test_invoice_list_filters_by_document_type(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2025-0001',
            'document_type' => 'invoice',
        ]);
        Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'CN-2025-0001',
            'document_type' => 'credit_note',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->set('documentTypeFilter', 'credit_note')
            ->assertSee('CN-2025-0001')
            ->assertDontSee('INV-2025-0001');
    }
}
