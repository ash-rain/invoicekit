<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\InvoiceValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceValidationService $service;

    private User $user;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceValidationService;
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'name' => 'Test EOOD',
            'address_line1' => 'ул. Тестова 1',
            'city' => 'София',
            'postal_code' => '1000',
            'registration_number' => '123456789',
        ]);
        $this->user->update(['current_company_id' => $this->company->id]);
    }

    public function test_complete_bg_invoice_passes_validation(): void
    {
        $invoice = $this->createCompleteBgInvoice();

        $result = $this->service->validate($invoice, $this->company);

        $this->assertTrue($result->passes(), 'Complete BG invoice should pass. Errors: '.json_encode($result->errors()));
    }

    public function test_missing_issue_date_fails(): void
    {
        $invoice = $this->createCompleteBgInvoice();
        $invoice->issue_date = null;

        $result = $this->service->validate($invoice, $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('issue_date'));
    }

    public function test_missing_tax_event_date_fails(): void
    {
        $invoice = $this->createCompleteBgInvoice(['tax_event_date' => null]);

        $result = $this->service->validate($invoice, $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('tax_event_date'));
    }

    public function test_missing_issued_by_name_fails(): void
    {
        $invoice = $this->createCompleteBgInvoice(['issued_by_name' => null]);

        $result = $this->service->validate($invoice, $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('issued_by_name'));
    }

    public function test_missing_received_by_name_fails(): void
    {
        $invoice = $this->createCompleteBgInvoice(['received_by_name' => null]);

        $result = $this->service->validate($invoice, $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('received_by_name'));
    }

    public function test_missing_payment_due_date_fails(): void
    {
        $invoice = $this->createCompleteBgInvoice(['payment_due_date' => null]);

        $result = $this->service->validate($invoice, $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('payment_due_date'));
    }

    public function test_missing_buyer_eik_for_bg_buyer_fails(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'vat_number' => null,
            'registration_number' => null,
        ]);
        $invoice = $this->createCompleteBgInvoice(['client_id' => $client->id]);

        $result = $this->service->validate($invoice, $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('buyer_eik_or_vat'));
    }

    public function test_non_bg_buyer_without_eik_passes(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'DE',
            'vat_number' => null,
            'registration_number' => null,
        ]);
        $invoice = $this->createCompleteBgInvoice(['client_id' => $client->id]);

        $result = $this->service->validate($invoice, $this->company);

        $this->assertEmpty($result->errorsForField('buyer_eik_or_vat'));
    }

    public function test_zero_rate_item_without_legal_basis_fails(): void
    {
        $invoice = $this->createCompleteBgInvoice(['vat_legal_basis' => null]);
        $invoice->items->first()->update(['vat_rate' => 0, 'vat_rate_key' => 'zero_export']);

        $result = $this->service->validate($invoice->fresh(['items']), $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('vat_legal_basis'));
    }

    public function test_zero_rate_item_with_legal_basis_passes(): void
    {
        $invoice = $this->createCompleteBgInvoice([
            'vat_legal_basis' => 'Доставка извън ЕС – чл. 28 от ЗДДС',
        ]);
        $invoice->items->first()->update(['vat_rate' => 0, 'vat_rate_key' => 'zero_export']);

        $result = $this->service->validate($invoice->fresh(['items']), $this->company);

        $this->assertEmpty($result->errorsForField('vat_legal_basis'));
    }

    public function test_credit_note_without_original_invoice_fails(): void
    {
        $invoice = $this->createCompleteBgInvoice([
            'document_type' => 'credit_note',
            'original_invoice_id' => null,
            'correction_reason' => null,
        ]);

        $result = $this->service->validate($invoice, $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('original_invoice_id'));
        $this->assertNotEmpty($result->errorsForField('correction_reason'));
    }

    public function test_credit_note_with_original_and_reason_passes(): void
    {
        $original = $this->createCompleteBgInvoice();
        $invoice = $this->createCompleteBgInvoice([
            'document_type' => 'credit_note',
            'original_invoice_id' => $original->id,
            'original_invoice_number' => $original->invoice_number,
            'original_invoice_date' => $original->issue_date,
            'correction_reason' => 'Грешка в количеството',
        ]);

        $result = $this->service->validate($invoice, $this->company);

        $this->assertEmpty($result->errorsForField('original_invoice_id'));
        $this->assertEmpty($result->errorsForField('correction_reason'));
    }

    public function test_can_issue_returns_false_when_validation_fails(): void
    {
        $invoice = $this->createCompleteBgInvoice();
        $invoice->issue_date = null;

        $this->assertFalse($this->service->canIssue($invoice, $this->company));
    }

    public function test_can_issue_returns_true_when_validation_passes(): void
    {
        $invoice = $this->createCompleteBgInvoice();

        $this->assertTrue($this->service->canIssue($invoice, $this->company));
    }

    public function test_custom_company_rules_are_merged(): void
    {
        $this->company->update([
            'custom_invoice_rules' => [
                'required_fields' => ['notes'],
                'field_labels' => ['notes' => 'Invoice Notes'],
            ],
        ]);

        $invoice = $this->createCompleteBgInvoice(['notes' => null]);

        $result = $this->service->validate($invoice, $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('notes'));
    }

    public function test_proforma_skips_most_validation(): void
    {
        $invoice = $this->createCompleteBgInvoice([
            'document_type' => 'proforma',
            'tax_event_date' => null,
            'issued_by_name' => null,
            'received_by_name' => null,
        ]);

        $result = $this->service->validate($invoice, $this->company);

        $this->assertTrue($result->passes(), 'Proforma should skip strict validation. Errors: '.json_encode($result->errors()));
    }

    public function test_invoice_without_line_items_fails(): void
    {
        $invoice = $this->createCompleteBgInvoice();
        $invoice->items()->delete();

        $result = $this->service->validate($invoice->fresh(['items']), $this->company);

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('line_items'));
    }

    public function test_client_completeness_bg_business_missing_eik(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'BG',
            'name' => 'Test Client',
            'address' => 'ул. Клиентска 5',
            'vat_number' => null,
            'registration_number' => null,
        ]);

        $result = $this->service->clientCompleteness($client, 'BG');

        $this->assertTrue($result->fails());
    }

    public function test_client_completeness_eu_business_with_vat_passes(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'DE',
            'name' => 'German GmbH',
            'address' => 'Berliner Str. 1',
            'vat_number' => 'DE123456789',
        ]);

        $result = $this->service->clientCompleteness($client, 'BG');

        $this->assertTrue($result->passes());
    }

    public function test_client_completeness_missing_address_fails(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'country' => 'US',
            'name' => 'US Corp',
            'address' => null,
        ]);

        $result = $this->service->clientCompleteness($client, 'BG');

        $this->assertTrue($result->fails());
        $this->assertNotEmpty($result->errorsForField('buyer_address'));
    }

    /**
     * Creates a complete BG invoice for testing. Null values for NOT NULL DB columns
     * (e.g. issue_date) should be set on the returned model directly in the test,
     * not passed as overrides.
     */
    private function createCompleteBgInvoice(array $overrides = []): Invoice
    {
        // Separate nullable overrides — applied via DB update after creation
        $nullableFields = array_filter($overrides, fn ($v) => $v === null);
        $nonNullOverrides = array_filter($overrides, fn ($v) => $v !== null);

        $client = isset($overrides['client_id'])
            ? Client::find($overrides['client_id'])
            : Client::factory()->create([
                'user_id' => $this->user->id,
                'country' => 'BG',
                'name' => 'Клиент ЕООД',
                'address' => 'ул. Клиентска 1, София',
                'registration_number' => '987654321',
            ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $defaults = [
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'invoice_number' => fake()->unique()->numerify('000000####'),
            'status' => 'draft',
            'issue_date' => '2026-04-11',
            'due_date' => '2026-05-11',
            'payment_due_date' => '2026-05-11',
            'currency' => 'BGN',
            'subtotal' => 1000.00,
            'vat_rate' => 20.00,
            'vat_amount' => 200.00,
            'total' => 1200.00,
            'vat_type' => 'standard',
            'document_type' => 'invoice',
            'tax_event_date' => '2026-04-11',
            'issued_by_name' => 'Иван Иванов',
            'received_by_name' => 'Петър Петров',
            'payment_method_id' => $paymentMethod->id,
            'payment_method_snapshot' => $paymentMethod->toSnapshot(),
            'language' => 'bg',
        ];

        $invoice = Invoice::factory()->create(array_merge($defaults, $nonNullOverrides));

        // Apply null overrides via query builder for nullable DB columns
        if (! empty($nullableFields)) {
            \Illuminate\Support\Facades\DB::table('invoices')
                ->where('id', $invoice->id)
                ->update($nullableFields);
        }

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Консултантски услуги',
            'unit' => 'бр.',
            'quantity' => 10,
            'unit_price' => 100.00,
            'vat_rate' => 20.00,
            'vat_rate_key' => 'standard',
            'total' => 1000.00,
        ]);

        return $invoice->fresh(['items', 'client']);
    }
}
