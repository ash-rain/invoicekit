<?php

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceList;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StornoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Company $company;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'invoice_numbering_format' => 'bg_sequential',
            'bg_invoice_sequence_start' => 1,
        ]);
        $this->user->update(['current_company_id' => $this->company->id]);

        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
        ]);

        $this->invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'invoice_number' => '0000000001',
            'status' => 'sent',
            'document_type' => 'invoice',
            'issue_date' => '2026-04-11',
            'subtotal' => 1000.00,
            'vat_rate' => 20.00,
            'vat_amount' => 200.00,
            'total' => 1200.00,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'description' => 'Service',
            'quantity' => 10,
            'unit_price' => 100.00,
            'vat_rate' => 20.00,
            'vat_rate_key' => 'standard',
            'total' => 1000.00,
        ]);
    }

    public function test_annulling_invoice_creates_credit_note(): void
    {
        $this->actingAs($this->user);

        Livewire::test(InvoiceList::class)
            ->call('annulInvoice', $this->invoice->id, 'Грешка в данъчната основа');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->user->id,
            'document_type' => 'credit_note',
            'original_invoice_id' => $this->invoice->id,
        ]);
    }

    public function test_annulling_sets_correction_reason_on_credit_note(): void
    {
        $this->actingAs($this->user);

        Livewire::test(InvoiceList::class)
            ->call('annulInvoice', $this->invoice->id, 'Грешка');

        $creditNote = Invoice::where('document_type', 'credit_note')
            ->where('original_invoice_id', $this->invoice->id)
            ->first();

        $this->assertNotNull($creditNote);
        $this->assertStringContainsString('0000000001', $creditNote->correction_reason);
    }

    public function test_annulling_marks_original_as_cancelled(): void
    {
        $this->actingAs($this->user);

        Livewire::test(InvoiceList::class)
            ->call('annulInvoice', $this->invoice->id, 'Тест');

        $this->invoice->refresh();

        $this->assertTrue($this->invoice->isCancelled());
        $this->assertNotNull($this->invoice->cancelled_at);
    }

    public function test_cannot_annul_already_cancelled_invoice(): void
    {
        $this->actingAs($this->user);

        $this->invoice->update(['cancelled_at' => now(), 'cancellation_reason' => 'Already cancelled']);

        Livewire::test(InvoiceList::class)
            ->call('annulInvoice', $this->invoice->id, 'Try again');

        // Should not create a second credit note
        $this->assertCount(0, Invoice::where('document_type', 'credit_note')
            ->where('original_invoice_id', $this->invoice->id)
            ->get());
    }

    public function test_credit_note_shares_fiscal_sequence(): void
    {
        $this->actingAs($this->user);

        Livewire::test(InvoiceList::class)
            ->call('annulInvoice', $this->invoice->id, 'Грешка');

        $creditNote = Invoice::where('document_type', 'credit_note')
            ->where('original_invoice_id', $this->invoice->id)
            ->first();

        $this->assertSame('0000000002', $creditNote->invoice_number);
    }
}
