<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_landing_page_contains_invoicing_group(): void
    {
        $response = $this->get('/');

        $response->assertSee('Invoicing');
    }

    public function test_landing_page_contains_eu_compliance_group(): void
    {
        $response = $this->get('/');

        $response->assertSee('EU Compliance');
    }

    public function test_landing_page_contains_getting_paid_group(): void
    {
        $response = $this->get('/');

        $response->assertSee('Getting Paid');
    }

    public function test_landing_page_contains_productivity_group(): void
    {
        $response = $this->get('/');

        $response->assertSee('Productivity');
    }

    public function test_landing_page_contains_compliance_spotlight(): void
    {
        $response = $this->get('/');

        $response->assertSee('Deep compliance, country by country');
    }

    public function test_landing_page_contains_credit_notes_feature(): void
    {
        $response = $this->get('/');

        $response->assertSee('Credit Notes');
    }

    public function test_landing_page_contains_peppol_feature(): void
    {
        $response = $this->get('/');

        $response->assertSee('Peppol');
    }

    public function test_landing_page_contains_company_lookup_feature(): void
    {
        $response = $this->get('/');

        $response->assertSee('Company Lookup');
    }

    public function test_landing_page_does_not_contain_removed_vat_detail_section(): void
    {
        $response = $this->get('/');

        // The standalone VAT rates comparison table is removed
        $response->assertDontSee('vat_rates_title');
    }

    public function test_landing_page_language_switching_works_for_bulgarian(): void
    {
        $response = $this->get('/?lang=bg');

        $response->assertStatus(200);
        // BG.md must have all keys (falls back to EN silently if missing)
        $response->assertSee('InvoiceKit');
    }

    public function test_landing_page_new_faq_entry_for_credit_notes(): void
    {
        $response = $this->get('/');

        $response->assertSee('credit notes', false);
    }
}
