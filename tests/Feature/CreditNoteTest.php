<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditNoteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Company $company;

    private Invoice $originalInvoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'invoice_numbering_format' => 'bg_sequential',
            'bg_invoice_sequence_start' => 1,
            'registration_number' => '123456789',
        ]);
        $this->user->update(['current_company_id' => $this->company->id]);

        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'registration_number' => '987654321',
        ]);

        $this->originalInvoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'invoice_number' => '0000000001',
            'status' => 'sent',
            'document_type' => 'invoice',
            'subtotal' => 1000.00,
            'vat_rate' => 20.00,
            'vat_amount' => 200.00,
            'total' => 1200.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $this->originalInvoice->id,
            'description' => 'Service',
            'quantity' => 10,
            'unit_price' => 100.00,
            'vat_rate' => 20.00,
            'total' => 1000.00,
        ]);
    }

    public function test_credit_note_stores_original_invoice_reference(): void
    {
        $this->actingAs($this->user);

        $creditNote = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->originalInvoice->client_id,
            'document_type' => 'credit_note',
            'original_invoice_id' => $this->originalInvoice->id,
            'original_invoice_number' => $this->originalInvoice->invoice_number,
            'original_invoice_date' => $this->originalInvoice->issue_date,
            'correction_reason' => 'Грешка в количеството',
            'invoice_number' => '0000000002',
        ]);

        $this->assertSame('credit_note', $creditNote->document_type);
        $this->assertSame($this->originalInvoice->id, $creditNote->original_invoice_id);
        $this->assertSame('0000000001', $creditNote->original_invoice_number);
        $this->assertSame('Грешка в количеството', $creditNote->correction_reason);
    }

    public function test_credit_note_shares_fiscal_numbering_sequence(): void
    {
        // Original invoice is 0000000001
        // Next fiscal document (credit note) should be 0000000002
        $nextNumber = Invoice::generateBulgarianNumber($this->user->id, $this->company);

        $this->assertSame('0000000002', $nextNumber);
    }

    public function test_cancelled_invoice_is_detected(): void
    {
        $this->originalInvoice->update([
            'cancelled_at' => now(),
            'cancellation_reason' => 'Анулирана',
        ]);

        $this->assertTrue($this->originalInvoice->isCancelled());
    }

    public function test_original_invoice_total_is_numeric(): void
    {
        $this->assertIsNumeric($this->originalInvoice->total);
        $this->assertGreaterThan(0, (float) $this->originalInvoice->total);
    }
}
