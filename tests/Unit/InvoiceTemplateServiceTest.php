<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Invoice;
use App\Services\InvoiceTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceTemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceTemplateService;
    }

    public function test_get_available_templates_returns_all_six_templates(): void
    {
        $templates = $this->service->getAvailableTemplates();

        $this->assertCount(6, $templates);
        $this->assertArrayHasKey('classic', $templates);
        $this->assertArrayHasKey('modern', $templates);
        $this->assertArrayHasKey('bold', $templates);
        $this->assertArrayHasKey('elegant', $templates);
        $this->assertArrayHasKey('compact', $templates);
        $this->assertArrayHasKey('stripe', $templates);
    }

    public function test_each_template_has_name_and_description(): void
    {
        foreach ($this->service->getAvailableTemplates() as $slug => $meta) {
            $this->assertArrayHasKey('name', $meta, "Template '{$slug}' is missing 'name'");
            $this->assertArrayHasKey('description', $meta, "Template '{$slug}' is missing 'description'");
        }
    }

    public function test_get_template_path_returns_correct_view_path(): void
    {
        $this->assertSame('invoices.templates.classic.pdf', $this->service->getTemplatePath('classic'));
        $this->assertSame('invoices.templates.modern.pdf', $this->service->getTemplatePath('modern'));
        $this->assertSame('invoices.templates.stripe.pdf', $this->service->getTemplatePath('stripe'));
    }

    public function test_get_template_path_falls_back_to_classic_for_unknown_slug(): void
    {
        $this->assertSame('invoices.templates.classic.pdf', $this->service->getTemplatePath('nonexistent'));
    }

    public function test_is_valid_template_returns_true_for_known_slug(): void
    {
        $this->assertTrue($this->service->isValidTemplate('classic'));
        $this->assertTrue($this->service->isValidTemplate('bold'));
    }

    public function test_is_valid_template_returns_false_for_unknown_slug(): void
    {
        $this->assertFalse($this->service->isValidTemplate('unknown'));
    }

    public function test_resolve_for_invoice_uses_invoice_template_first(): void
    {
        $invoice = new Invoice(['template' => 'elegant']);
        $company = new Company(['invoice_template' => 'bold']);

        $slug = $this->service->resolveForInvoice($invoice, $company);

        $this->assertSame('elegant', $slug);
    }

    public function test_resolve_for_invoice_falls_back_to_company_template(): void
    {
        $invoice = new Invoice(['template' => null]);
        $company = new Company(['invoice_template' => 'bold']);

        $slug = $this->service->resolveForInvoice($invoice, $company);

        $this->assertSame('bold', $slug);
    }

    public function test_resolve_for_invoice_falls_back_to_classic_when_both_null(): void
    {
        $invoice = new Invoice(['template' => null]);
        $company = new Company(['invoice_template' => null]);

        $slug = $this->service->resolveForInvoice($invoice, $company);

        $this->assertSame('classic', $slug);
    }

    public function test_resolve_for_invoice_returns_classic_when_both_are_null(): void
    {
        $slug = $this->service->resolveForInvoice(null, null);

        $this->assertSame('classic', $slug);
    }
}
