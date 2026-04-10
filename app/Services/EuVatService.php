<?php

namespace App\Services;

class EuVatService
{
    /**
     * Standard VAT rates by EU country (ISO 2-letter code).
     */
    private array $vatRates = [
        'AT' => 20,
        'BE' => 21,
        'BG' => 20,
        'CY' => 19,
        'CZ' => 21,
        'DE' => 19,
        'DK' => 25,
        'EE' => 22,
        'EL' => 24,
        'ES' => 21,
        'FI' => 25.5,
        'FR' => 20,
        'HR' => 25,
        'HU' => 27,
        'IE' => 23,
        'IT' => 22,
        'LT' => 21,
        'LU' => 17,
        'LV' => 21,
        'MT' => 18,
        'NL' => 21,
        'PL' => 23,
        'PT' => 23,
        'RO' => 19,
        'SE' => 25,
        'SI' => 22,
        'SK' => 23,
    ];

    /**
     * Calculate VAT for a transaction.
     *
     * @param  string  $sellerCountry  ISO 2-letter code of the seller's country
     * @param  string  $buyerCountry  ISO 2-letter code of the buyer's country
     * @param  bool  $buyerHasVat  Whether the buyer has a valid EU VAT number
     * @param  float  $amount  Net amount (before VAT)
     * @param  bool  $sellerIsVatExempt  Whether the seller operates under a small-business VAT exemption
     * @return array{rate: float, amount: float, type: string}
     */
    public function calculateVat(
        string $sellerCountry,
        string $buyerCountry,
        bool $buyerHasVat,
        float $amount,
        bool $sellerIsVatExempt = false
    ): array {
        // Small-business VAT exemption — seller is not registered for VAT
        if ($sellerIsVatExempt) {
            return [
                'rate' => 0.0,
                'amount' => 0.0,
                'type' => 'vat_exempt',
            ];
        }

        $sellerCountry = strtoupper($sellerCountry);
        $buyerCountry = strtoupper($buyerCountry);

        // Same country — always apply local VAT
        if ($sellerCountry === $buyerCountry) {
            $rate = $this->vatRates[$sellerCountry] ?? 0;

            return [
                'rate' => (float) $rate,
                'amount' => round($amount * $rate / 100, 2),
                'type' => 'standard',
            ];
        }

        $buyerIsEu = isset($this->vatRates[$buyerCountry]);

        // Non-EU buyer — exempt
        if (! $buyerIsEu) {
            return [
                'rate' => 0.0,
                'amount' => 0.0,
                'type' => 'exempt',
            ];
        }

        // EU business buyer with a VAT number — reverse charge
        if ($buyerHasVat) {
            return [
                'rate' => 0.0,
                'amount' => 0.0,
                'type' => 'reverse_charge',
            ];
        }

        // EU consumer (no VAT number) — OSS: apply seller's country rate
        $rate = $this->vatRates[$sellerCountry] ?? 0;

        return [
            'rate' => (float) $rate,
            'amount' => round($amount * $rate / 100, 2),
            'type' => 'oss',
        ];
    }

    /**
     * Return the VAT rate for a given country, or null if not an EU country.
     */
    public function rateForCountry(string $countryCode): int|float|null
    {
        return $this->vatRates[strtoupper($countryCode)] ?? null;
    }

    /**
     * Check whether a country code is a known EU member.
     */
    public function isEuCountry(string $countryCode): bool
    {
        return isset($this->vatRates[strtoupper($countryCode)]);
    }
}
