<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\UblXmlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UblXmlTest extends TestCase
{
    use RefreshDatabase;

    // ── Auth guard ────────────────────────────────────────────────────────────

    public function test_xml_download_requires_auth(): void
    {
        $invoice = Invoice::factory()->create();

        $this->get(route('invoices.xml', $invoice))
            ->assertRedirect(route('login'));
    }

    public function test_xml_download_forbidden_for_other_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->get(route('invoices.xml', $invoice))
            ->assertForbidden();
    }

    // ── XML download ──────────────────────────────────────────────────────────

    public function test_xml_download_returns_xml_response(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)
            ->get(route('invoices.xml', $invoice));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertHeader('Content-Disposition', 'attachment; filename="invoice-'.$invoice->invoice_number.'.xml"');
    }

    public function test_xml_download_contains_invoice_number(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->get(route('invoices.xml', $invoice));

        $response->assertOk();
        $this->assertStringContainsString($invoice->invoice_number, $response->getContent());
    }

    // ── UblXmlService unit ────────────────────────────────────────────────────

    public function test_service_generates_valid_xml(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'vat_number' => 'DE123456789']);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2024-0001',
            'currency' => 'EUR',
            'subtotal' => '100.00',
            'vat_rate' => '19.00',
            'vat_amount' => '19.00',
            'total' => '119.00',
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Consulting service',
            'quantity' => '2.00',
            'unit_price' => '50.00',
            'total' => '100.00',
        ]);

        $service = app(UblXmlService::class);
        $xml = $service->generate($invoice);

        $dom = new \DOMDocument;
        $this->assertTrue($dom->loadXML($xml), 'Generated XML should be valid');

        $this->assertStringContainsString('INV-2024-0001', $xml);
        $this->assertStringContainsString('peppol.eu', $xml);
        $this->assertStringContainsString('380', $xml); // InvoiceTypeCode
        $this->assertStringContainsString('EUR', $xml);
        $this->assertStringContainsString('Consulting service', $xml);
    }

    public function test_service_uses_peppol_bis_customization_id(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $xml = app(UblXmlService::class)->generate($invoice);

        $this->assertStringContainsString('urn:cen.eu:en16931:2017', $xml);
        $this->assertStringContainsString('peppol.eu:2017:poacc:billing:3.0', $xml);
    }

    public function test_service_uses_exempt_tax_category_for_vat_exempt_invoice(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'vat_rate' => '0.00',
            'vat_amount' => '0.00',
            'vat_exempt_applied' => true,
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $xml = app(UblXmlService::class)->generate($invoice);

        // Tax category 'E' for exempt
        $this->assertStringContainsString('<cbc:ID>E</cbc:ID>', $xml);
        $this->assertStringContainsString('<cbc:Percent>0</cbc:Percent>', $xml);
    }

    public function test_service_includes_buyer_vat_number_when_present(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'vat_number' => 'FR87654321']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $xml = app(UblXmlService::class)->generate($invoice);

        $this->assertStringContainsString('FR87654321', $xml);
    }

    public function test_service_includes_seller_info_from_company(): void
    {
        $user = User::factory()->create();
        $company = \App\Models\Company::factory()->create([
            'user_id' => $user->id,
            'name' => 'ACME Corp',
            'vat_number' => 'BG123456789',
        ]);
        $user->update(['current_company_id' => $company->id]);

        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $xml = app(UblXmlService::class)->generate($invoice);

        $this->assertStringContainsString('ACME Corp', $xml);
        $this->assertStringContainsString('BG123456789', $xml);
    }

    public function test_service_generates_one_invoice_line_per_item(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        InvoiceItem::factory()->count(3)->create(['invoice_id' => $invoice->id]);

        $xml = app(UblXmlService::class)->generate($invoice);

        $dom = new \DOMDocument;
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $lines = $xpath->query('//cac:InvoiceLine');

        $this->assertSame(3, $lines->length);
    }

    // ── Multi-rate TaxSubtotal (BG-23) ──────────────────────────────────────────

    public function test_service_generates_one_tax_subtotal_per_vat_summary_group(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'subtotal' => '1100.00',
            'vat_rate' => null,
            'vat_amount' => '281.00',
            'total' => '1381.00',
            'vat_summary' => [
                ['rate' => 20.0, 'base' => 1000.0, 'vat' => 200.0, 'label' => 'Standard 20%'],
                ['rate' => 9.0, 'base' => 100.0, 'vat' => 81.0, 'label' => 'Reduced 9%'],
            ],
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'vat_rate' => '20.00',
            'total' => '1000.00',
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'vat_rate' => '9.00',
            'total' => '100.00',
        ]);

        $xml = app(UblXmlService::class)->generate($invoice);

        $dom = new \DOMDocument;
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        $subtotals = $xpath->query('//cac:TaxTotal/cac:TaxSubtotal');
        $this->assertSame(2, $subtotals->length);

        $taxableAmounts = [];
        $taxAmounts = [];
        $percents = [];
        foreach ($subtotals as $subtotal) {
            $taxableAmounts[] = $xpath->evaluate('string(cbc:TaxableAmount)', $subtotal);
            $taxAmounts[] = $xpath->evaluate('string(cbc:TaxAmount)', $subtotal);
            $percents[] = $xpath->evaluate('string(cac:TaxCategory/cbc:Percent)', $subtotal);
        }

        $this->assertContains('1000', $taxableAmounts);
        $this->assertContains('100', $taxableAmounts);
        $this->assertContains('200', $taxAmounts);
        $this->assertContains('81', $taxAmounts);
        $this->assertContains('20', $percents);
        $this->assertContains('9', $percents);
    }

    public function test_service_generates_single_tax_subtotal_when_vat_summary_absent(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'subtotal' => '100.00',
            'vat_rate' => '19.00',
            'vat_amount' => '19.00',
            'total' => '119.00',
            'vat_summary' => null,
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'total' => '100.00']);

        $xml = app(UblXmlService::class)->generate($invoice);

        $dom = new \DOMDocument;
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        $subtotals = $xpath->query('//cac:TaxTotal/cac:TaxSubtotal');
        $this->assertSame(1, $subtotals->length);
    }

    // ── Line-level ClassifiedTaxCategory (BG-25) ─────────────────────────────────

    public function test_line_classified_tax_category_reflects_item_vat_rate(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'vat_rate' => null,
            'vat_summary' => [
                ['rate' => 20.0, 'base' => 1000.0, 'vat' => 200.0, 'label' => 'Standard 20%'],
                ['rate' => 9.0, 'base' => 100.0, 'vat' => 9.0, 'label' => 'Reduced 9%'],
            ],
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'vat_rate' => '20.00',
            'total' => '1000.00',
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'vat_rate' => '9.00',
            'total' => '100.00',
        ]);

        $xml = app(UblXmlService::class)->generate($invoice);

        $dom = new \DOMDocument;
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        $percents = $xpath->query('//cac:InvoiceLine/cac:Item/cac:ClassifiedTaxCategory/cbc:Percent');
        $this->assertSame(2, $percents->length);

        $values = [];
        foreach ($percents as $percent) {
            $values[] = $percent->textContent;
        }

        $this->assertContains('20.00', $values);
        $this->assertContains('9.00', $values);
    }

    // ── PaymentMeans / IBAN (BT-84) ──────────────────────────────────────────────

    public function test_payment_means_included_when_company_has_iban(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'bank_iban' => 'BG80BNBG96611020345678',
        ]);
        $user->update(['current_company_id' => $company->id]);

        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $xml = app(UblXmlService::class)->generate($invoice);

        $dom = new \DOMDocument;
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        $paymentMeans = $xpath->query('//cac:PaymentMeans');
        $this->assertSame(1, $paymentMeans->length);

        $iban = $xpath->evaluate('string(//cac:PaymentMeans/cac:PayeeFinancialAccount/cbc:ID)');
        $this->assertSame('BG80BNBG96611020345678', $iban);
    }

    public function test_payment_means_omitted_when_company_has_no_iban(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'bank_iban' => null,
        ]);
        $user->update(['current_company_id' => $company->id]);

        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $xml = app(UblXmlService::class)->generate($invoice);

        $dom = new \DOMDocument;
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        $paymentMeans = $xpath->query('//cac:PaymentMeans');
        $this->assertSame(0, $paymentMeans->length);
    }
}
