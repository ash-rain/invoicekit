<?php

namespace App\Services;

class VatExemptionService
{
    /**
     * Return the full exemption config for a given ISO 2-letter country code.
     *
     * @return array<string, mixed>|null null if country is not in the config
     */
    public function getExemptionForCountry(string $isoCode): ?array
    {
        $data = config('vat_exemptions.'.strtoupper($isoCode));

        return is_array($data) ? $data : null;
    }

    /**
     * Return whether the small-business VAT exemption is available for a country.
     */
    public function isExemptionAvailable(string $isoCode): bool
    {
        $data = $this->getExemptionForCountry($isoCode);

        return $data !== null && ($data['available'] ?? false) === true;
    }

    /**
     * Return the invoice notice text for the given country and language.
     *
     * @param  string  $language  'local' or 'en'
     */
    public function getInvoiceNotice(string $isoCode, string $language = 'local'): ?string
    {
        $data = $this->getExemptionForCountry($isoCode);

        if ($data === null || ! ($data['available'] ?? false)) {
            return null;
        }

        if ($language === 'en') {
            return $data['invoice_notice_en'] ?? null;
        }

        return $data['invoice_notice_local'] ?? null;
    }
}
