<?php

namespace App\Services;

use App\Exceptions\NoAvailableApiKeyException;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompanyLookupService
{
    private const VIES_URL = 'https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat-number';

    private const MAX_GEMINI_ATTEMPTS = 2;

    /** EU country codes that have a VIES VAT prefix. */
    private const VIES_COUNTRIES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
        'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
    ];

    public function __construct(
        private readonly AiKeyRotationService $rotation,
    ) {}

    /**
     * Resolve a company by VAT number or national registration number.
     *
     * @param  string  $input  Raw input (e.g. "DE123456789", "203137077", "BG203137077")
     * @param  string|null  $countryHint  Two-letter country code from the form's country field
     * @param  User|null  $user  Used for BYOK and limit enforcement
     * @return array{found: bool, name: string|null, address: string|null, country: string|null, vat_number: string|null, registration_number: string|null, vat_registered: bool, source: string|null}
     */
    public function lookup(string $input, ?string $countryHint = null, ?User $user = null, ?string $forceType = null): array
    {
        $input = trim($input);

        if ($input === '') {
            return $this->notFound();
        }

        $detected = $this->detectInputType($input, $countryHint, $forceType);

        if ($detected === null) {
            return $this->notFound();
        }

        ['country' => $country, 'number' => $number, 'type' => $type] = $detected;

        // Layer 1: VIES (EU VAT numbers only, free and unlimited)
        if ($type === 'vat' && in_array($country, self::VIES_COUNTRIES, true)) {
            $viesResult = $this->lookupVies($country, $number);

            if ($viesResult !== null) {
                return $viesResult;
            }

            // VIES confirmed the number is not a valid VAT registration.
            // If the country prefix was auto-added (i.e. the user entered a
            // plain numeric identifier), strip the prefix and switch to a
            // registration-number search so Gemini finds the right company.
            $inputUpper = strtoupper($input);
            $viesPrefix = ($country === 'GR') ? 'EL' : $country;

            if (! str_starts_with($inputUpper, $viesPrefix) && ! str_starts_with($inputUpper, $country)) {
                $number = preg_replace('/^[A-Z]{2}/i', '', $number);
                $type = 'registration';
            }
        }

        // Layer 2: Gemini AI (rate-limited per plan)
        return $this->lookupGemini($country, $number, $type, $user);
    }

    /**
     * Determine whether input looks like a VAT number or a registration number,
     * and extract the country code and numeric part.
     *
     * When $forceType is 'registration', the country code is never prepended to
     * the number — this prevents Gemini from confusing a national registration
     * identifier (e.g. Bulgarian EIK) with a VAT number that contains the same
     * digits.
     *
     * @return array{country: string, number: string, type: string}|null
     */
    public function detectInputType(string $input, ?string $countryHint = null, ?string $forceType = null): ?array
    {
        $input = strtoupper(trim($input));

        // Two-letter EU prefix (e.g. "DE123456789", "BG203137077")
        if (preg_match('/^([A-Z]{2})([A-Z0-9]+)$/', $input, $m)) {
            $prefix = $m[1];
            $number = $m[2];

            // Greek VAT uses "EL" prefix in VIES, mapped to GR internally
            $country = ($prefix === 'EL') ? 'GR' : $prefix;

            // Forced registration lookup — strip the country prefix so Gemini
            // searches by the bare national identifier (e.g. EIK 203137077).
            if ($forceType === 'registration') {
                return ['country' => $country, 'number' => $number, 'type' => 'registration'];
            }

            if (in_array($prefix, self::VIES_COUNTRIES, true) || $prefix === 'EL') {
                return ['country' => $country, 'number' => $prefix.$number, 'type' => 'vat'];
            }

            // Non-EU alpha prefix that looks like a country code — treat as registration
            return ['country' => $country, 'number' => $input, 'type' => 'registration'];
        }

        // Pure numeric input — use countryHint to determine type
        if (preg_match('/^\d{7,18}$/', $input)) {
            $country = strtoupper($countryHint ?? 'BG');

            // Forced registration lookup — return the bare number without any
            // country prefix so it goes straight to Gemini as a national ID.
            if ($forceType === 'registration') {
                return ['country' => $country, 'number' => $input, 'type' => 'registration'];
            }

            // For EU countries with a VIES prefix, build the VAT candidate and try VIES first
            if (in_array($country, self::VIES_COUNTRIES, true)) {
                return ['country' => $country, 'number' => $country.$input, 'type' => 'vat'];
            }

            return ['country' => $country, 'number' => $input, 'type' => 'registration'];
        }

        // Alphanumeric with special chars (e.g. "HRB 12345", "CHE-123.456.789")
        if ($countryHint !== null && preg_match('/^[A-Z0-9\-\. \/]+$/i', $input)) {
            return ['country' => strtoupper($countryHint), 'number' => $input, 'type' => 'registration'];
        }

        return null;
    }

    /**
     * Check a VAT number against the EU VIES REST API.
     *
     * @return array{found: bool, name: string|null, address: string|null, country: string, vat_number: string, registration_number: string|null, vat_registered: bool, source: string}|null
     */
    public function lookupVies(string $countryCode, string $vatNumber): ?array
    {
        // VIES uses "EL" for Greece
        $viesCountry = ($countryCode === 'GR') ? 'EL' : $countryCode;

        // Strip the country prefix from the number before sending
        $numberOnly = preg_replace('/^[A-Z]{2}/i', '', $vatNumber);

        try {
            $response = Http::timeout(15)->post(self::VIES_URL, [
                'countryCode' => $viesCountry,
                'vatNumber' => $numberOnly,
            ]);
        } catch (ConnectionException) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        if (! ($data['valid'] ?? false)) {
            return null;
        }

        $name = $data['name'] ?? null;

        // VIES sometimes returns "---" for unavailable data
        if ($name === '---') {
            $name = null;
        }

        $address = $data['address'] ?? null;

        if ($address === '---') {
            $address = null;
        }

        return [
            'found' => true,
            'name' => $name,
            'address' => $address,
            'country' => $countryCode,
            'vat_number' => strtoupper($vatNumber),
            'registration_number' => null,
            'vat_registered' => true,
            'source' => 'vies',
        ];
    }

    /**
     * Look up a company using Gemini AI as a fallback.
     *
     * @return array{found: bool, name: string|null, address: string|null, country: string|null, vat_number: string|null, registration_number: string|null, vat_registered: bool, source: string}
     */
    public function lookupGemini(string $country, string $number, string $idType, ?User $user = null): array
    {
        // Check and enforce lookup limits (BYOK bypasses limits)
        if ($user !== null && ! $user->gemini_api_key) {
            if (! $this->canUserLookup($user)) {
                return array_merge($this->notFound(), ['limit_reached' => true]);
            }
        }

        $cacheKey = 'company_lookup:'.md5(strtolower($country.':'.$idType.':'.$number));

        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $prompt = $this->buildLookupPrompt($country, $number, $idType);

        $result = null;
        $lastError = null;

        if ($user?->gemini_api_key) {
            try {
                $result = $this->callGeminiWithRawKey($user->gemini_api_key, $prompt);
            } catch (\Throwable $e) {
                Log::warning('CompanyLookupService Gemini (BYOK) failed', ['error' => $e->getMessage()]);

                return $this->notFound();
            }
        } else {
            for ($attempt = 1; $attempt <= self::MAX_GEMINI_ATTEMPTS; $attempt++) {
                try {
                    $key = $this->rotation->getNextKey();
                    $result = $this->callGemini($key->api_key, $prompt);
                    $this->rotation->markUsed($key);
                    break;
                } catch (NoAvailableApiKeyException) {
                    return $this->notFound();
                } catch (\Throwable $e) {
                    $lastError = $e;

                    if (isset($key)) {
                        $this->rotation->markFailed($key, substr($e->getMessage(), 0, 500));
                    }
                }
            }

            if ($result === null) {
                Log::warning('CompanyLookupService Gemini failed after retries', [
                    'country' => $country,
                    'number' => $number,
                    'error' => $lastError?->getMessage(),
                ]);

                return $this->notFound();
            }
        }

        // Consume a lookup slot after a successful call (not on cache hits)
        if ($user !== null && ! $user->gemini_api_key) {
            $this->incrementLookupCount($user);
        }

        if (! ($result['found'] ?? false)) {
            return $this->notFound();
        }

        // Always use the country we searched in — Gemini's country field is
        // unreliable and may return a different country whose VAT contains the
        // same digits (e.g. returning AT instead of BG for EIK 203137077).
        $output = [
            'found' => true,
            'name' => $result['name'] ?? null,
            'address' => $result['address'] ?? null,
            'country' => $country,
            'vat_number' => $result['vat_number'] ?? null,
            'registration_number' => $result['registration_number'] ?? null,
            'vat_registered' => (bool) ($result['vat_registered'] ?? false),
            'source' => 'gemini',
        ];

        Cache::put($cacheKey, $output, now()->addDay());

        return $output;
    }

    /**
     * Check whether a user has remaining Gemini lookup quota for today.
     */
    public function canUserLookup(User $user): bool
    {
        $plan = $user->plan ?? 'free';
        $limit = config('ai.lookup_limits.'.$plan);

        if ($limit === null) {
            return true;
        }

        $used = (int) Cache::get($this->lookupCacheKey($user), 0);

        return $used < $limit;
    }

    /**
     * Increment the user's daily Gemini lookup counter.
     */
    public function incrementLookupCount(User $user): void
    {
        $key = $this->lookupCacheKey($user);
        $ttl = now()->endOfDay()->timestamp - now()->timestamp;

        Cache::add($key, 0, $ttl);
        Cache::increment($key);
    }

    /**
     * Return the number of Gemini lookups remaining today for a user.
     */
    public function remainingLookups(User $user): ?int
    {
        if ($user->gemini_api_key) {
            return null; // BYOK — unlimited
        }

        $plan = $user->plan ?? 'free';
        $limit = config('ai.lookup_limits.'.$plan);

        if ($limit === null) {
            return null; // pro — unlimited
        }

        $used = (int) Cache::get($this->lookupCacheKey($user), 0);

        return max(0, $limit - $used);
    }

    private function lookupCacheKey(User $user): string
    {
        return 'lookup_count:'.$user->id.':'.now()->toDateString();
    }

    private function buildLookupPrompt(string $country, string $number, string $idType): string
    {
        $countryName = \Locale::getDisplayRegion('-'.$country, 'en') ?: $country;
        $context = config('country_defaults.'.$country.'.lookup_gemini_context', '');
        $label = $idType === 'vat' ? 'VAT number' : 'registration number';

        $lines = [
            "Look up the company with {$label} {$number} registered in {$countryName}.",
        ];

        if ($context !== '') {
            $lines[] = $context;
        }

        if ($idType === 'registration') {
            $lines[] = "IMPORTANT: Search by the national registration/company identifier (e.g. EIK, IČO, KRS), NOT by VAT number. A different company may have a VAT number that contains these same digits — do not return that company. Only return the company whose national registration ID is exactly {$number}.";
        }

        $lines[] = 'If you cannot find reliable, verifiable data for this specific number, return found=false.';
        $lines[] = 'Do not guess or invent company data.';
        $lines[] = "Return ONLY a single JSON object (no markdown, no explanation) with these keys: found (boolean), name (string or null), address (full postal address string or null), country (always \"{$country}\"), registration_number (national company ID string or null), vat_number (string or null), vat_registered (boolean).";

        return implode(' ', $lines);
    }

    /** @return array<string, mixed> */
    private function callGemini(string $apiKey, string $prompt): array
    {
        $payload = $this->buildTextPayload($prompt);
        $endpoint = config('ai.gemini.endpoint');

        try {
            $response = Http::timeout(30)->post("{$endpoint}?key={$apiKey}", $payload);
        } catch (ConnectionException $e) {
            throw new \RuntimeException('Connection to Gemini API timed out.', 0, $e);
        }

        return $this->parseGeminiResponse($response);
    }

    /** @return array<string, mixed> */
    private function callGeminiWithRawKey(string $apiKey, string $prompt): array
    {
        return $this->callGemini($apiKey, $prompt);
    }

    /** @return array<string, mixed> */
    private function buildTextPayload(string $prompt): array
    {
        // Google Search grounding + structured output (response_schema) cannot be
        // combined on Gemini 2.5 — that combo requires Gemini 3+.  We therefore
        // rely on prompt instructions for the JSON format and extract JSON from
        // the (possibly grounded) text response.
        return [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'tools' => [
                ['google_search' => new \stdClass],
            ],
            'generationConfig' => [
                'temperature' => 0.1,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function parseGeminiResponse(\Illuminate\Http\Client\Response $response): array
    {
        if ($response->status() === 429) {
            throw new \RuntimeException('Rate limit exceeded (429).');
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new \RuntimeException('Invalid or unauthorized API key ('.$response->status().').');
        }

        if ($response->failed()) {
            $error = data_get($response->json(), 'error.message', 'Unknown API error');
            throw new \RuntimeException("Gemini API error: {$error}");
        }

        // When Google Search grounding is enabled the response text may contain
        // surrounding prose or markdown fences around the JSON object. We need
        // to extract the JSON robustly.
        $text = '';

        foreach (data_get($response->json(), 'candidates.0.content.parts', []) as $part) {
            if (isset($part['text'])) {
                $text .= $part['text'];
            }
        }

        if (empty($text)) {
            throw new \RuntimeException('Gemini returned an empty response.');
        }

        $json = $this->extractJson($text);

        if ($json === null) {
            throw new \RuntimeException('Failed to extract JSON from Gemini response.');
        }

        return $json;
    }

    /**
     * Extract a JSON object from text that may contain markdown fences or prose.
     *
     * @return array<string, mixed>|null
     */
    private function extractJson(string $text): ?array
    {
        // 1. Try direct decode (model returned pure JSON)
        $decoded = json_decode(trim($text), true);

        if (is_array($decoded)) {
            return $decoded;
        }

        // 2. Strip markdown code fences: ```json ... ``` or ``` ... ```
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $m)) {
            $decoded = json_decode($m[1], true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // 3. Find the first top-level JSON object by matching balanced braces
        $start = strpos($text, '{');

        if ($start !== false) {
            $depth = 0;
            $len = strlen($text);

            for ($i = $start; $i < $len; $i++) {
                if ($text[$i] === '{') {
                    $depth++;
                } elseif ($text[$i] === '}') {
                    $depth--;

                    if ($depth === 0) {
                        $candidate = substr($text, $start, $i - $start + 1);
                        $decoded = json_decode($candidate, true);

                        if (is_array($decoded)) {
                            return $decoded;
                        }

                        break;
                    }
                }
            }
        }

        return null;
    }

    /** @return array{found: bool, name: null, address: null, country: null, vat_number: null, registration_number: null, vat_registered: false, source: null} */
    private function notFound(): array
    {
        return [
            'found' => false,
            'name' => null,
            'address' => null,
            'country' => null,
            'vat_number' => null,
            'registration_number' => null,
            'vat_registered' => false,
            'source' => null,
        ];
    }
}
