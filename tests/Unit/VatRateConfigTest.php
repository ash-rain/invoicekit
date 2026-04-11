<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class VatRateConfigTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = require __DIR__.'/../../config/vat_rates.php';
    }

    public function test_bg_has_standard_rate_of_20(): void
    {
        $this->assertArrayHasKey('BG', $this->config);
        $this->assertArrayHasKey('standard', $this->config['BG']);
        $this->assertSame(20, $this->config['BG']['standard']['rate']);
    }

    public function test_bg_has_reduced_tourism_rate_of_9(): void
    {
        $this->assertArrayHasKey('reduced_tourism', $this->config['BG']);
        $this->assertSame(9, $this->config['BG']['reduced_tourism']['rate']);
        $this->assertNotEmpty($this->config['BG']['reduced_tourism']['legal_ref']);
    }

    public function test_bg_zero_rate_categories_have_legal_refs(): void
    {
        $zeroRateKeys = ['zero_export', 'zero_intra_eu'];
        foreach ($zeroRateKeys as $key) {
            $this->assertArrayHasKey($key, $this->config['BG'], "Missing BG key: {$key}");
            $this->assertSame(0, $this->config['BG'][$key]['rate']);
            $this->assertNotEmpty($this->config['BG'][$key]['legal_ref'], "Empty legal_ref for BG.{$key}");
        }
    }

    public function test_bg_exempt_categories_have_legal_refs(): void
    {
        $exemptKeys = [
            'exempt_financial', 'exempt_insurance', 'exempt_education', 'exempt_healthcare',
        ];
        foreach ($exemptKeys as $key) {
            $this->assertArrayHasKey($key, $this->config['BG'], "Missing BG key: {$key}");
            $this->assertSame(0, $this->config['BG'][$key]['rate']);
            $this->assertNotEmpty($this->config['BG'][$key]['legal_ref'], "Empty legal_ref for BG.{$key}");
            $this->assertNotEmpty($this->config['BG'][$key]['label'], "Empty label for BG.{$key}");
        }
    }

    public function test_every_rate_entry_has_required_fields(): void
    {
        foreach ($this->config as $country => $rates) {
            foreach ($rates as $key => $entry) {
                $this->assertArrayHasKey('rate', $entry, "Missing rate for {$country}.{$key}");
                $this->assertArrayHasKey('label', $entry, "Missing label for {$country}.{$key}");
                $this->assertIsNumeric($entry['rate'], "Non-numeric rate for {$country}.{$key}");

                if ($entry['rate'] === 0 || $entry['rate'] === 0.0) {
                    $this->assertArrayHasKey('legal_ref', $entry, "Zero-rate {$country}.{$key} missing legal_ref");
                    $this->assertNotEmpty($entry['legal_ref'], "Empty legal_ref for zero-rate {$country}.{$key}");
                }
            }
        }
    }

    public function test_all_27_eu_countries_have_standard_rate(): void
    {
        $euCountries = [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL', 'ES', 'FI', 'FR', 'HR',
            'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
        ];
        foreach ($euCountries as $country) {
            $this->assertArrayHasKey($country, $this->config, "Missing country: {$country}");
            $this->assertArrayHasKey('standard', $this->config[$country], "Missing standard rate for: {$country}");
        }
    }
}
