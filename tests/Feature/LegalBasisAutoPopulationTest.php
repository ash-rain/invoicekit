<?php

namespace Tests\Feature;

use App\Services\VatRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalBasisAutoPopulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vat_legal_bases_config_has_bg_zero_rate_keys(): void
    {
        $config = require base_path('config/vat_legal_bases.php');

        $this->assertArrayHasKey('reverse_charge', $config['BG']);
        $this->assertArrayHasKey('export_non_eu', $config['BG']);
        $this->assertArrayHasKey('intra_eu_supply', $config['BG']);
        $this->assertArrayHasKey('exempt_financial', $config['BG']);
    }

    public function test_export_non_eu_legal_basis_contains_law_reference(): void
    {
        $config = require base_path('config/vat_legal_bases.php');

        $this->assertArrayHasKey('export_non_eu', $config['BG']);
        $this->assertStringContainsString('чл.', $config['BG']['export_non_eu']);
    }

    public function test_intra_eu_supply_legal_basis_contains_law_reference(): void
    {
        $config = require base_path('config/vat_legal_bases.php');

        $this->assertStringContainsString('чл.', $config['BG']['intra_eu_supply']);
    }

    public function test_reverse_charge_legal_basis_contains_law_reference(): void
    {
        $config = require base_path('config/vat_legal_bases.php');

        $this->assertStringContainsString('чл.', $config['BG']['reverse_charge']);
    }

    public function test_vat_rate_service_returns_zero_for_zero_export_key(): void
    {
        $service = app(VatRateService::class);

        $rate = $service->rateForKey('BG', 'zero_export');

        $this->assertEquals(0, $rate);
    }

    public function test_vat_rate_service_returns_zero_for_zero_intra_eu_key(): void
    {
        $service = app(VatRateService::class);

        $rate = $service->rateForKey('BG', 'zero_intra_eu');

        $this->assertEquals(0, $rate);
    }

    public function test_vat_rate_service_returns_zero_for_exempt_financial_key(): void
    {
        $service = app(VatRateService::class);

        $rate = $service->rateForKey('BG', 'exempt_financial');

        $this->assertEquals(0, $rate);
    }

    public function test_key_map_covers_all_bg_zero_rate_vat_rate_keys(): void
    {
        $service = app(VatRateService::class);
        $rates = $service->ratesForCountry('BG');
        $legalBases = require base_path('config/vat_legal_bases.php');
        $countryBases = $legalBases['BG'];

        $keyMap = [
            'zero_export' => 'export_non_eu',
            'zero_intra_eu' => 'intra_eu_supply',
        ];

        foreach ($rates as $key => $rateData) {
            if (($rateData['rate'] ?? null) === 0) {
                $legalKey = $keyMap[$key] ?? $key;
                $this->assertArrayHasKey(
                    $legalKey,
                    $countryBases,
                    "Zero-rate key '{$key}' (maps to '{$legalKey}') has no legal basis entry in config/vat_legal_bases.php for BG"
                );
            }
        }
    }
}
