<?php

namespace Tests\Unit;

use App\Services\VatExemptionService;
use Tests\TestCase;

class VatExemptionServiceTest extends TestCase
{
    private VatExemptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VatExemptionService;
    }

    public function test_returns_exemption_config_for_valid_country(): void
    {
        $result = $this->service->getExemptionForCountry('DE');

        $this->assertIsArray($result);
        $this->assertTrue($result['available']);
        $this->assertArrayHasKey('threshold_amount', $result);
        $this->assertArrayHasKey('legal_basis', $result);
        $this->assertArrayHasKey('invoice_notice_local', $result);
        $this->assertArrayHasKey('invoice_notice_en', $result);
    }

    public function test_returns_null_for_unknown_country(): void
    {
        $this->assertNull($this->service->getExemptionForCountry('XX'));
    }

    public function test_exemption_available_returns_true_for_eligible_country(): void
    {
        $this->assertTrue($this->service->isExemptionAvailable('DE'));
        $this->assertTrue($this->service->isExemptionAvailable('FR'));
        $this->assertTrue($this->service->isExemptionAvailable('BG'));
    }

    public function test_exemption_not_available_for_spain(): void
    {
        $this->assertFalse($this->service->isExemptionAvailable('ES'));
    }

    public function test_exemption_not_available_for_unknown_country(): void
    {
        $this->assertFalse($this->service->isExemptionAvailable('XX'));
    }

    public function test_invoice_notice_returns_local_text(): void
    {
        $notice = $this->service->getInvoiceNotice('DE', 'local');

        $this->assertNotNull($notice);
        $this->assertIsString($notice);
        $this->assertNotEmpty($notice);
    }

    public function test_invoice_notice_returns_english_text(): void
    {
        $notice = $this->service->getInvoiceNotice('DE', 'en');

        $this->assertNotNull($notice);
        $this->assertIsString($notice);
        $this->assertNotEmpty($notice);
    }

    public function test_invoice_notice_returns_null_for_spain(): void
    {
        $this->assertNull($this->service->getInvoiceNotice('ES'));
    }

    public function test_invoice_notice_returns_null_for_unknown_country(): void
    {
        $this->assertNull($this->service->getInvoiceNotice('XX'));
    }

    public function test_config_covers_all_required_eu_countries(): void
    {
        $euCountries = [
            'AT',
            'BE',
            'BG',
            'HR',
            'CY',
            'CZ',
            'DK',
            'EE',
            'FI',
            'FR',
            'DE',
            'GR',
            'HU',
            'IE',
            'IT',
            'LV',
            'LT',
            'LU',
            'MT',
            'NL',
            'PL',
            'PT',
            'RO',
            'SK',
            'SI',
            'SE',
        ];

        foreach ($euCountries as $code) {
            $data = $this->service->getExemptionForCountry($code);
            $this->assertNotNull($data, "Missing config for country: {$code}");
            $this->assertArrayHasKey('available', $data, "Missing 'available' key for {$code}");
        }
    }
}
