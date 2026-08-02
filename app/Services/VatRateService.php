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
        $rates = $this->rates[strtoupper($countryCode)] ?? [];

        return array_filter($rates, fn (array $entry) => $this->isCurrentlyActive($entry));
    }

    /**
     * A rate entry is active when today falls within its optional valid_from/valid_until
     * window. Entries without a window are always active.
     */
    private function isCurrentlyActive(array $entry): bool
    {
        $today = date('Y-m-d');

        if (isset($entry['valid_from']) && $today < $entry['valid_from']) {
            return false;
        }

        if (isset($entry['valid_until']) && $today > $entry['valid_until']) {
            return false;
        }

        return true;
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
