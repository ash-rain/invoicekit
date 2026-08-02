<?php

namespace Tests\Unit;

use App\Services\VatRateService;
use PHPUnit\Framework\TestCase;

class VatRateServiceTest extends TestCase
{
    private VatRateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VatRateService;
    }

    public function test_rates_for_country_returns_bg_rates(): void
    {
        $rates = $this->service->ratesForCountry('BG');

        $this->assertArrayHasKey('standard', $rates);
        $this->assertArrayHasKey('reduced_tourism', $rates);
        $this->assertArrayHasKey('zero_export', $rates);
    }

    public function test_rates_for_unknown_country_returns_empty(): void
    {
        $rates = $this->service->ratesForCountry('XX');

        $this->assertEmpty($rates);
    }

    public function test_rate_for_key_returns_numeric_rate(): void
    {
        $rate = $this->service->rateForKey('BG', 'standard');

        $this->assertSame(20, $rate);
    }

    public function test_rate_for_key_returns_null_for_unknown(): void
    {
        $this->assertNull($this->service->rateForKey('BG', 'nonexistent'));
        $this->assertNull($this->service->rateForKey('XX', 'standard'));
    }

    public function test_legal_ref_for_key_returns_ref(): void
    {
        $ref = $this->service->legalRefForKey('BG', 'reduced_tourism');

        $this->assertStringContainsString('чл. 66', $ref);
    }

    public function test_legal_ref_for_standard_rate_returns_null(): void
    {
        $ref = $this->service->legalRefForKey('BG', 'standard');

        $this->assertNull($ref);
    }

    public function test_dropdown_options_returns_formatted_array(): void
    {
        $options = $this->service->dropdownOptions('BG');

        $this->assertNotEmpty($options);
        $first = $options[0];
        $this->assertArrayHasKey('key', $first);
        $this->assertArrayHasKey('rate', $first);
        $this->assertArrayHasKey('label', $first);
    }

    public function test_country_codes_are_case_insensitive(): void
    {
        $upper = $this->service->ratesForCountry('BG');
        $lower = $this->service->ratesForCountry('bg');

        $this->assertEquals($upper, $lower);
    }

    // ── Date-windowed (temporary) rates ─────────────────────────────────────

    public function test_rate_with_future_valid_from_is_excluded(): void
    {
        $service = new VatRateService($this->fixturePath([
            'XX' => [
                'standard' => ['rate' => 20, 'label' => 'Standard'],
                'future_temp' => ['rate' => 5, 'label' => 'Future temp', 'valid_from' => '2099-01-01'],
            ],
        ]));

        $rates = $service->ratesForCountry('XX');

        $this->assertArrayHasKey('standard', $rates);
        $this->assertArrayNotHasKey('future_temp', $rates);
    }

    public function test_rate_with_past_valid_until_is_excluded(): void
    {
        $service = new VatRateService($this->fixturePath([
            'XX' => [
                'standard' => ['rate' => 20, 'label' => 'Standard'],
                'expired_temp' => ['rate' => 5, 'label' => 'Expired temp', 'valid_until' => '2000-01-01'],
            ],
        ]));

        $rates = $service->ratesForCountry('XX');

        $this->assertArrayNotHasKey('expired_temp', $rates);
    }

    public function test_rate_within_valid_window_is_included(): void
    {
        $service = new VatRateService($this->fixturePath([
            'XX' => [
                'active_temp' => ['rate' => 6, 'label' => 'Active temp', 'valid_from' => '2000-01-01', 'valid_until' => '2099-01-01'],
            ],
        ]));

        $rates = $service->ratesForCountry('XX');

        $this->assertArrayHasKey('active_temp', $rates);
    }

    public function test_sweden_temporary_food_rate_is_currently_active(): void
    {
        // 2026-04-01 – 2027-12-31 window; "today" per the app's current date is 2026-07-31.
        $rate = $this->service->rateForKey('SE', 'reduced_food_temporary');

        $this->assertSame(6, $rate);
    }

    private function fixturePath(array $rates): string
    {
        $path = sys_get_temp_dir().'/vat_rates_'.uniqid().'.php';
        file_put_contents($path, '<?php return '.var_export($rates, true).';');

        return $path;
    }
}
