<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class VatLegalBasesConfigTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = require __DIR__.'/../../config/vat_legal_bases.php';
    }

    public function test_bg_has_all_required_scenarios(): void
    {
        $requiredKeys = [
            'reverse_charge',
            'export_non_eu',
            'exempt_small_business',
            'reduced_9_tourism',
            'intra_eu_supply',
            'exempt_financial',
            'exempt_insurance',
            'exempt_education',
            'exempt_healthcare',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $this->config['BG'], "Missing BG legal basis: {$key}");
            $this->assertIsString($this->config['BG'][$key], "BG.{$key} is not a string");
            $this->assertNotEmpty($this->config['BG'][$key], "BG.{$key} is empty");
        }
    }

    public function test_bg_texts_contain_legal_article_references(): void
    {
        // Every BG legal basis must reference a ЗДДС article
        foreach ($this->config['BG'] as $key => $text) {
            $this->assertMatchesRegularExpression(
                '/чл\.|ЗДДС/',
                $text,
                "BG.{$key} does not reference a ЗДДС article: {$text}"
            );
        }
    }

    public function test_de_has_reverse_charge_and_small_business(): void
    {
        $this->assertArrayHasKey('DE', $this->config);
        $this->assertNotEmpty($this->config['DE']['reverse_charge']);
        $this->assertNotEmpty($this->config['DE']['exempt_small_business']);
    }

    public function test_every_country_has_at_least_reverse_charge_and_export(): void
    {
        foreach ($this->config as $country => $bases) {
            $this->assertArrayHasKey('reverse_charge', $bases, "Missing reverse_charge for {$country}");
            $this->assertArrayHasKey('export_non_eu', $bases, "Missing export_non_eu for {$country}");
            $this->assertNotEmpty($bases['reverse_charge'], "Empty reverse_charge for {$country}");
            $this->assertNotEmpty($bases['export_non_eu'], "Empty export_non_eu for {$country}");
        }
    }
}
