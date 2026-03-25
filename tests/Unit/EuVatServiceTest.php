<?php

namespace Tests\Unit;

use App\Services\EuVatService;
use PHPUnit\Framework\TestCase;

class EuVatServiceTest extends TestCase
{
    private EuVatService $vat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vat = new EuVatService();
    }

    // ──────────────────────────────────────────────────────────────────
    // Same-country transactions → standard rate
    // ──────────────────────────────────────────────────────────────────

    public function test_same_country_applies_local_vat_rate(): void
    {
        $result = $this->vat->calculateVat('DE', 'DE', false, 100.00);

        $this->assertSame('standard', $result['type']);
        $this->assertSame(19.0, $result['rate']);
        $this->assertSame(19.0, $result['amount']);
    }

    public function test_same_country_with_vat_number_still_applies_standard_rate(): void
    {
        $result = $this->vat->calculateVat('FR', 'FR', true, 200.00);

        $this->assertSame('standard', $result['type']);
        $this->assertSame(20.0, $result['rate']);
        $this->assertSame(40.0, $result['amount']);
    }

    /**
     * @dataProvider sameCountryVatRatesProvider
     */
    public function test_same_country_rates_match_plan(string $country, int $expectedRate): void
    {
        $result = $this->vat->calculateVat($country, $country, false, 100.00);

        $this->assertSame('standard', $result['type']);
        $this->assertSame((float) $expectedRate, $result['rate']);
        $this->assertEqualsWithDelta($expectedRate, $result['amount'], 0.001);
    }

    public static function sameCountryVatRatesProvider(): array
    {
        return [
            'BG' => ['BG', 20],
            'DE' => ['DE', 19],
            'FR' => ['FR', 20],
            'RO' => ['RO', 19],
            'PL' => ['PL', 23],
            'CZ' => ['CZ', 21],
            'IT' => ['IT', 22],
            'ES' => ['ES', 21],
            'NL' => ['NL', 21],
            'PT' => ['PT', 23],
            'AT' => ['AT', 20],
            'BE' => ['BE', 21],
            'HR' => ['HR', 25],
            'HU' => ['HU', 27],
            'SE' => ['SE', 25],
        ];
    }

    // ──────────────────────────────────────────────────────────────────
    // Non-EU buyers → exempt (0%)
    // ──────────────────────────────────────────────────────────────────

    public function test_non_eu_buyer_without_vat_is_exempt(): void
    {
        $result = $this->vat->calculateVat('DE', 'US', false, 100.00);

        $this->assertSame('exempt', $result['type']);
        $this->assertSame(0.0, $result['rate']);
        $this->assertSame(0.0, $result['amount']);
    }

    public function test_non_eu_buyer_with_vat_number_is_still_exempt(): void
    {
        $result = $this->vat->calculateVat('FR', 'GB', true, 500.00);

        $this->assertSame('exempt', $result['type']);
        $this->assertSame(0.0, $result['rate']);
        $this->assertSame(0.0, $result['amount']);
    }

    /**
     * @dataProvider nonEuCountryProvider
     */
    public function test_various_non_eu_buyers_are_exempt(string $buyerCountry): void
    {
        $result = $this->vat->calculateVat('DE', $buyerCountry, false, 100.00);

        $this->assertSame('exempt', $result['type']);
    }

    public static function nonEuCountryProvider(): array
    {
        return [
            'US' => ['US'],
            'GB' => ['GB'],
            'CH' => ['CH'],
            'NO' => ['NO'],
            'AU' => ['AU'],
            'CA' => ['CA'],
        ];
    }

    // ──────────────────────────────────────────────────────────────────
    // EU business buyer with VAT number → reverse charge (0%)
    // ──────────────────────────────────────────────────────────────────

    public function test_eu_business_buyer_with_vat_number_gets_reverse_charge(): void
    {
        $result = $this->vat->calculateVat('DE', 'FR', true, 100.00);

        $this->assertSame('reverse_charge', $result['type']);
        $this->assertSame(0.0, $result['rate']);
        $this->assertSame(0.0, $result['amount']);
    }

    /**
     * @dataProvider sameCountryVatRatesProvider
     */
    public function test_eu_business_buyers_across_all_eu_countries_get_reverse_charge(string $buyerCountry, int $ignored): void
    {
        // Pick a different seller country (BG as default seller)
        $sellerCountry = $buyerCountry === 'BG' ? 'DE' : 'BG';

        $result = $this->vat->calculateVat($sellerCountry, $buyerCountry, true, 1000.00);

        $this->assertSame('reverse_charge', $result['type']);
        $this->assertSame(0.0, $result['rate']);
        $this->assertSame(0.0, $result['amount']);
    }

    // ──────────────────────────────────────────────────────────────────
    // EU consumer buyer without VAT number → OSS (seller's rate)
    // ──────────────────────────────────────────────────────────────────

    public function test_eu_consumer_without_vat_number_gets_oss_rate(): void
    {
        // DE seller (19%) selling to FR consumer (no VAT)
        $result = $this->vat->calculateVat('DE', 'FR', false, 100.00);

        $this->assertSame('oss', $result['type']);
        $this->assertSame(19.0, $result['rate']);
        $this->assertSame(19.0, $result['amount']);
    }

    public function test_oss_uses_sellers_country_rate_not_buyers(): void
    {
        // BG seller (20%) selling to HU consumer (27%)
        $result = $this->vat->calculateVat('BG', 'HU', false, 100.00);

        $this->assertSame('oss', $result['type']);
        $this->assertSame(20.0, $result['rate']);  // seller BG rate
        $this->assertSame(20.0, $result['amount']);
    }

    public function test_oss_calculation_for_different_seller_rates(): void
    {
        // PL seller (23%) selling to IT consumer (no VAT)
        $result = $this->vat->calculateVat('PL', 'IT', false, 200.00);

        $this->assertSame('oss', $result['type']);
        $this->assertSame(23.0, $result['rate']);
        $this->assertEqualsWithDelta(46.0, $result['amount'], 0.001);
    }

    // ──────────────────────────────────────────────────────────────────
    // Amount rounding
    // ──────────────────────────────────────────────────────────────────

    public function test_vat_amount_is_rounded_to_two_decimal_places(): void
    {
        // 19% of 10.005 = 1.9009... → should round correctly
        $result = $this->vat->calculateVat('DE', 'DE', false, 10.005);

        // Amount should not have more than 2 decimal places of precision
        $this->assertEqualsWithDelta(round($result['amount'], 2), $result['amount'], 0.0001);
    }

    public function test_zero_amount_returns_zero_vat(): void
    {
        $result = $this->vat->calculateVat('DE', 'DE', false, 0.00);

        $this->assertSame(0.0, $result['amount']);
    }

    // ──────────────────────────────────────────────────────────────────
    // Case insensitivity
    // ──────────────────────────────────────────────────────────────────

    public function test_country_codes_are_case_insensitive(): void
    {
        $upper = $this->vat->calculateVat('DE', 'DE', false, 100.00);
        $lower = $this->vat->calculateVat('de', 'de', false, 100.00);
        $mixed = $this->vat->calculateVat('De', 'dE', false, 100.00);

        $this->assertSame($upper['rate'], $lower['rate']);
        $this->assertSame($upper['rate'], $mixed['rate']);
        $this->assertSame($upper['type'], $lower['type']);
    }

    // ──────────────────────────────────────────────────────────────────
    // Helper methods
    // ──────────────────────────────────────────────────────────────────

    public function test_rate_for_eu_country_returns_correct_rate(): void
    {
        $this->assertSame(19, $this->vat->rateForCountry('DE'));
        $this->assertSame(20, $this->vat->rateForCountry('BG'));
        $this->assertSame(25, $this->vat->rateForCountry('HR'));
        $this->assertSame(27, $this->vat->rateForCountry('HU'));
    }

    public function test_rate_for_non_eu_country_returns_null(): void
    {
        $this->assertNull($this->vat->rateForCountry('US'));
        $this->assertNull($this->vat->rateForCountry('GB'));
        $this->assertNull($this->vat->rateForCountry('XX'));
    }

    public function test_is_eu_country_returns_true_for_eu_members(): void
    {
        foreach (['BG', 'DE', 'FR', 'RO', 'PL', 'CZ', 'IT', 'ES', 'NL', 'PT', 'AT', 'BE', 'HR', 'HU', 'SE'] as $code) {
            $this->assertTrue($this->vat->isEuCountry($code), "Expected {$code} to be EU");
        }
    }

    public function test_is_eu_country_returns_false_for_non_eu_countries(): void
    {
        foreach (['US', 'GB', 'CH', 'NO', 'JP', 'AU', 'CA', 'XX'] as $code) {
            $this->assertFalse($this->vat->isEuCountry($code), "Expected {$code} to be non-EU");
        }
    }

    public function test_is_eu_country_is_case_insensitive(): void
    {
        $this->assertTrue($this->vat->isEuCountry('de'));
        $this->assertTrue($this->vat->isEuCountry('De'));
        $this->assertFalse($this->vat->isEuCountry('us'));
    }

    // ──────────────────────────────────────────────────────────────────
    // Return shape
    // ──────────────────────────────────────────────────────────────────

    public function test_result_always_contains_required_keys(): void
    {
        $scenarios = [
            ['DE', 'DE', false],  // standard
            ['DE', 'FR', true],   // reverse_charge
            ['DE', 'FR', false],  // oss
            ['DE', 'US', false],  // exempt
        ];

        foreach ($scenarios as [$seller, $buyer, $hasVat]) {
            $result = $this->vat->calculateVat($seller, $buyer, $hasVat, 100.00);
            $this->assertArrayHasKey('rate', $result);
            $this->assertArrayHasKey('amount', $result);
            $this->assertArrayHasKey('type', $result);
        }
    }

    public function test_type_is_always_one_of_the_four_valid_values(): void
    {
        $validTypes = ['standard', 'reverse_charge', 'oss', 'exempt'];

        $scenarios = [
            ['DE', 'DE', false],
            ['DE', 'FR', true],
            ['DE', 'FR', false],
            ['DE', 'US', false],
        ];

        foreach ($scenarios as [$seller, $buyer, $hasVat]) {
            $result = $this->vat->calculateVat($seller, $buyer, $hasVat, 100.00);
            $this->assertContains($result['type'], $validTypes, "Unexpected type: {$result['type']}");
        }
    }
}
