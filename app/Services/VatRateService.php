<?php

namespace App\Services;

class VatRateService
{
    private array $rates;

    public function __construct(?string $configPath = null)
    {
        $path = $configPath ?? __DIR__.'/../../config/vat_rates.php';
        $this->rates = require $path;
    }

    /** @return array<string, array{rate: int|float, label: string, legal_ref?: string}> */
    public function ratesForCountry(string $countryCode): array
    {
        return $this->rates[strtoupper($countryCode)] ?? [];
    }

    public function rateForKey(string $countryCode, string $key): int|float|null
    {
        return $this->ratesForCountry($countryCode)[$key]['rate'] ?? null;
    }

    public function legalRefForKey(string $countryCode, string $key): ?string
    {
        return $this->ratesForCountry($countryCode)[$key]['legal_ref'] ?? null;
    }

    /** @return array<int, array{key: string, rate: int|float, label: string, legal_ref: ?string}> */
    public function dropdownOptions(string $countryCode): array
    {
        $options = [];
        foreach ($this->ratesForCountry($countryCode) as $key => $entry) {
            $options[] = [
                'key' => $key,
                'rate' => $entry['rate'],
                'label' => $entry['label'],
                'legal_ref' => $entry['legal_ref'] ?? null,
            ];
        }

        return $options;
    }
}
