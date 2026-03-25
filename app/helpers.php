<?php

if (! function_exists('formatCurrency')) {
    /**
     * Format a monetary amount with the correct currency symbol and format.
     *
     * @param  string  $currency  ISO 4217 currency code
     * @param  float  $amount  The amount to format
     */
    function formatCurrency(string $currency, float $amount): string
    {
        $formatted = number_format(abs($amount), 2);
        $sign = $amount < 0 ? '-' : '';

        return match (strtoupper($currency)) {
            'EUR' => "{$sign}€{$formatted}",
            'USD' => "{$sign}\${$formatted}",
            'BGN' => "{$sign}{$formatted} лв.",
            'RON' => "{$sign}{$formatted} RON",
            'PLN' => "{$sign}{$formatted} zł",
            'CZK' => "{$sign}{$formatted} Kč",
            'HUF' => "{$sign}{$formatted} Ft",
            default => "{$sign}{$currency} {$formatted}",
        };
    }
}
