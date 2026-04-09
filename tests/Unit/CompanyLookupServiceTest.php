<?php

namespace Tests\Unit\tests\Unit;

use App\Models\AiApiKey;
use App\Models\User;
use App\Services\AiKeyRotationService;
use App\Services\CompanyLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CompanyLookupServiceTest extends TestCase
{
    use RefreshDatabase;

    private CompanyLookupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CompanyLookupService(new AiKeyRotationService);
    }

    // ──────────────────────────────────────────────────────────────────
    // Input detection
    // ──────────────────────────────────────────────────────────────────

    public function test_detects_eu_vat_number_with_country_prefix(): void
    {
        $result = $this->service->detectInputType('DE123456789');

        $this->assertNotNull($result);
        $this->assertSame('DE', $result['country']);
        $this->assertSame('DE123456789', $result['number']);
        $this->assertSame('vat', $result['type']);
    }

    public function test_detects_bulgarian_eik_nine_digits_as_vat_attempt_with_hint(): void
    {
        $result = $this->service->detectInputType('203137077', 'BG');

        $this->assertNotNull($result);
        $this->assertSame('BG', $result['country']);
        $this->assertSame('vat', $result['type']); // BG is a VIES country, so tries VAT first
    }

    public function test_detects_pure_numeric_input_without_hint_defaults_to_bg(): void
    {
        $result = $this->service->detectInputType('203137077');

        $this->assertNotNull($result);
        $this->assertSame('BG', $result['country']);
    }

    public function test_detects_greek_vat_with_el_prefix(): void
    {
        $result = $this->service->detectInputType('EL123456789');

        $this->assertNotNull($result);
        $this->assertSame('GR', $result['country']);
        $this->assertSame('vat', $result['type']);
    }

    public function test_returns_null_for_empty_input(): void
    {
        $this->assertNull($this->service->detectInputType(''));
        $this->assertNull($this->service->detectInputType('   '));
    }

    public function test_detects_alphanumeric_registration_number_with_country_hint(): void
    {
        $result = $this->service->detectInputType('HRB 12345', 'DE');

        $this->assertNotNull($result);
        $this->assertSame('DE', $result['country']);
        $this->assertSame('registration', $result['type']);
    }

    // ──────────────────────────────────────────────────────────────────
    // VIES lookup
    // ──────────────────────────────────────────────────────────────────

    public function test_vies_returns_company_data_on_valid_vat(): void
    {
        Http::fake([
            'ec.europa.eu/*' => Http::response([
                'valid' => true,
                'name' => 'Acme GmbH',
                'address' => 'Musterstraße 1 12345 Berlin',
            ], 200),
        ]);

        $result = $this->service->lookupVies('DE', 'DE123456789');

        $this->assertNotNull($result);
        $this->assertTrue($result['found']);
        $this->assertSame('Acme GmbH', $result['name']);
        $this->assertSame('DE', $result['country']);
        $this->assertSame('DE123456789', $result['vat_number']);
        $this->assertTrue($result['vat_registered']);
        $this->assertSame('vies', $result['source']);
    }

    public function test_vies_returns_null_when_vat_not_valid(): void
    {
        Http::fake([
            'ec.europa.eu/*' => Http::response(['valid' => false], 200),
        ]);

        $result = $this->service->lookupVies('DE', 'DE000000000');

        $this->assertNull($result);
    }

    public function test_vies_returns_null_on_http_failure(): void
    {
        Http::fake([
            'ec.europa.eu/*' => Http::response([], 500),
        ]);

        $result = $this->service->lookupVies('DE', 'DE123456789');

        $this->assertNull($result);
    }

    public function test_vies_maps_greek_country_to_gr(): void
    {
        Http::fake([
            'ec.europa.eu/*' => Http::response([
                'valid' => true,
                'name' => 'Greek Corp',
                'address' => 'Athens',
            ], 200),
        ]);

        $result = $this->service->lookupVies('GR', 'EL123456789');

        $this->assertNotNull($result);
        $this->assertSame('GR', $result['country']); // Returned as GR, not EL
    }

    public function test_vies_normalises_triple_dash_name_to_null(): void
    {
        Http::fake([
            'ec.europa.eu/*' => Http::response([
                'valid' => true,
                'name' => '---',
                'address' => 'Some Street',
            ], 200),
        ]);

        $result = $this->service->lookupVies('DE', 'DE123456789');

        $this->assertNotNull($result);
        $this->assertNull($result['name']);
    }

    // ──────────────────────────────────────────────────────────────────
    // Gemini lookup
    // ──────────────────────────────────────────────────────────────────

    public function test_gemini_returns_company_data_when_found(): void
    {
        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'found' => true,
                                'name' => 'Netshell EOOD',
                                'address' => 'БЪЛГАРИЯ, гр. Казанлък (6100)',
                                'country' => 'BG',
                                'registration_number' => '203137077',
                                'vat_number' => null,
                                'vat_registered' => false,
                            ]),
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $result = $this->service->lookupGemini('BG', '203137077', 'registration');

        $this->assertTrue($result['found']);
        $this->assertSame('Netshell EOOD', $result['name']);
        $this->assertSame('gemini', $result['source']);
        $this->assertFalse($result['vat_registered']);
    }

    public function test_gemini_returns_not_found_when_api_says_found_false(): void
    {
        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode(['found' => false]),
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $result = $this->service->lookupGemini('BG', '999999999', 'registration');

        $this->assertFalse($result['found']);
    }

    public function test_gemini_caches_successful_results(): void
    {
        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        $geminiResponseBody = json_encode([
            'found' => true,
            'name' => 'Cached Corp',
            'address' => 'Test St 1',
            'country' => 'DE',
            'registration_number' => null,
            'vat_number' => 'DE123456789',
            'vat_registered' => true,
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push([
                    'candidates' => [[
                        'content' => ['parts' => [['text' => $geminiResponseBody]]],
                    ]],
                ])
                ->push(['error' => ['message' => 'Should not be called again']]),
        ]);

        // First call hits Gemini
        $result1 = $this->service->lookupGemini('DE', 'DE123456789', 'vat');
        // Second call comes from cache
        $result2 = $this->service->lookupGemini('DE', 'DE123456789', 'vat');

        $this->assertTrue($result1['found']);
        $this->assertTrue($result2['found']);
        Http::assertSentCount(1); // Only one HTTP call made
    }

    // ──────────────────────────────────────────────────────────────────
    // BG EIK 203137077 — NETSHELL EOOD (our own company)
    // ──────────────────────────────────────────────────────────────────

    public function test_lookup_bg_eik_203137077_returns_netshell(): void
    {
        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        // VIES returns invalid (non-VAT-registered company), then Gemini is called
        Http::fake([
            'ec.europa.eu/*' => Http::response(['valid' => false], 200),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'found' => true,
                                'name' => 'NETSHELL EOOD',
                                'address' => 'БЪЛГАРИЯ, гр. Казанлък (6100), Старозагорска, 13, бл. ., вх. А, ет. 4, ап. 8',
                                'country' => 'BG',
                                'registration_number' => '203137077',
                                'vat_number' => null,
                                'vat_registered' => false,
                            ]),
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $result = $this->service->lookup('203137077', 'BG');

        $this->assertTrue($result['found']);
        $this->assertSame('NETSHELL EOOD', $result['name']);
        $this->assertStringContainsString('Казанлък', $result['address']);
        $this->assertSame('BG', $result['country']);
        $this->assertSame('203137077', $result['registration_number']);
        $this->assertNull($result['vat_number']);
        $this->assertFalse($result['vat_registered']);
        $this->assertSame('gemini', $result['source']);

        // Verify Gemini was called with the bare numeric ID (not BG203137077) as a
        // registration number, so it doesn't confuse it with a VAT number.
        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), 'generativelanguage')) {
                return false;
            }

            $body = json_decode($request->body(), true);
            $prompt = $body['contents'][0]['parts'][0]['text'] ?? '';

            // The primary lookup line must reference "registration number 203137077"
            return str_contains($prompt, 'registration number 203137077')
                && ! str_starts_with($prompt, 'Look up the company with VAT number BG203137077');
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // Full lookup chain
    // ──────────────────────────────────────────────────────────────────

    public function test_vies_success_skips_gemini(): void
    {
        Http::fake([
            'ec.europa.eu/*' => Http::response([
                'valid' => true,
                'name' => 'Verified GmbH',
                'address' => 'Berlin',
            ], 200),
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Should not be called'], 500),
        ]);

        $result = $this->service->lookup('DE123456789', 'DE');

        $this->assertTrue($result['found']);
        $this->assertSame('vies', $result['source']);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'ec.europa.eu'));
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'generativelanguage'));
    }

    public function test_vies_failure_falls_through_to_gemini(): void
    {
        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Http::fake([
            'ec.europa.eu/*' => Http::response(['valid' => false], 200),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'found' => true,
                                'name' => 'Fallback Corp',
                                'address' => 'Test',
                                'country' => 'DE',
                                'registration_number' => '12345',
                                'vat_number' => null,
                                'vat_registered' => false,
                            ]),
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $result = $this->service->lookup('DE123456789', 'DE');

        $this->assertTrue($result['found']);
        $this->assertSame('gemini', $result['source']);
    }

    // ──────────────────────────────────────────────────────────────────
    // Lookup limits
    // ──────────────────────────────────────────────────────────────────

    public function test_free_user_can_lookup_within_limit(): void
    {
        $user = User::factory()->free()->create();

        $this->assertTrue($this->service->canUserLookup($user));
    }

    public function test_free_user_is_blocked_after_reaching_limit(): void
    {
        $user = User::factory()->free()->create();

        $limit = config('ai.lookup_limits.free');
        Cache::put('lookup_count:'.$user->id.':'.now()->toDateString(), $limit, now()->endOfDay());

        $this->assertFalse($this->service->canUserLookup($user));
    }

    public function test_starter_user_has_higher_limit_than_free(): void
    {
        $user = User::factory()->starter()->create();

        // Simulate free-plan limit used (2) — starter should still pass
        Cache::put('lookup_count:'.$user->id.':'.now()->toDateString(), config('ai.lookup_limits.free'), now()->endOfDay());

        $this->assertTrue($this->service->canUserLookup($user));
    }

    public function test_pro_user_is_never_blocked(): void
    {
        $user = User::factory()->pro()->create();

        Cache::put('lookup_count:'.$user->id.':'.now()->toDateString(), 9999, now()->endOfDay());

        $this->assertTrue($this->service->canUserLookup($user));
    }

    public function test_increment_lookup_count_tracks_daily_usage(): void
    {
        $user = User::factory()->free()->create();

        $this->service->incrementLookupCount($user);
        $this->service->incrementLookupCount($user);

        $cacheKey = 'lookup_count:'.$user->id.':'.now()->toDateString();
        $this->assertSame(2, Cache::get($cacheKey));
    }

    public function test_remaining_lookups_decrements_after_use(): void
    {
        $user = User::factory()->free()->create();

        $this->service->incrementLookupCount($user);

        $remaining = $this->service->remainingLookups($user);

        $this->assertSame(config('ai.lookup_limits.free') - 1, $remaining);
    }

    public function test_byok_user_has_null_remaining_indicating_unlimited(): void
    {
        $user = User::factory()->free()->create(['gemini_api_key' => 'user-own-key']);

        $this->assertNull($this->service->remainingLookups($user));
    }

    public function test_gemini_lookup_returns_limit_reached_flag_when_at_cap(): void
    {
        $user = User::factory()->free()->create();

        $limit = config('ai.lookup_limits.free');
        Cache::put('lookup_count:'.$user->id.':'.now()->toDateString(), $limit, now()->endOfDay());

        $result = $this->service->lookupGemini('BG', '203137077', 'registration', $user);

        $this->assertFalse($result['found']);
        $this->assertTrue($result['limit_reached'] ?? false);
    }

    public function test_force_registration_type_skips_vies_and_strips_country_prefix(): void
    {
        // BG203137077 would normally auto-detect as a VAT number and try VIES.
        // When forceType='registration', VIES must be skipped entirely and the
        // bare number (without "BG" prefix) should reach Gemini as a registration id.
        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => json_encode([
                        'found' => true,
                        'name' => 'Test Company EOOD',
                        'address' => 'Sofia, Bulgaria',
                        'country' => 'BG',
                        'vat_number' => null,
                        'registration_number' => '203137077',
                        'vat_registered' => false,
                    ])]]],
                ]],
            ], 200),
        ]);

        $result = $this->service->lookup('BG203137077', 'BG', null, 'registration');

        $this->assertTrue($result['found']);
        $this->assertSame('BG', $result['country']);
        $this->assertSame('203137077', $result['registration_number']);
        $this->assertNull($result['vat_number']);

        // VIES must not be called at all
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'ec.europa.eu'));

        // Gemini must receive the bare number as a registration identifier
        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), 'generativelanguage')) {
                return false;
            }

            $body = json_decode($request->body(), true);
            $prompt = $body['contents'][0]['parts'][0]['text'] ?? '';

            return str_contains($prompt, 'registration number 203137077')
                && ! str_starts_with($prompt, 'Look up the company with VAT number BG203137077');
        });
    }

    public function test_force_registration_with_pure_numeric_eik_skips_vies(): void
    {
        // This is the exact UI code path: user toggles "EIK" and enters a bare
        // numeric identifier like "203137077". The number must NOT get "BG"
        // prepended, VIES must be skipped, and Gemini must search by the bare
        // national registration ID.
        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => json_encode([
                        'found' => true,
                        'name' => 'NETSHELL EOOD',
                        'address' => 'БЪЛГАРИЯ, гр. Казанлък (6100)',
                        'country' => 'BG',
                        'registration_number' => '203137077',
                        'vat_number' => null,
                        'vat_registered' => false,
                    ])]]],
                ]],
            ], 200),
        ]);

        $result = $this->service->lookup('203137077', 'BG', null, 'registration');

        $this->assertTrue($result['found']);
        $this->assertSame('NETSHELL EOOD', $result['name']);
        $this->assertSame('BG', $result['country']);
        $this->assertSame('203137077', $result['registration_number']);
        $this->assertFalse($result['vat_registered']);
        $this->assertSame('gemini', $result['source']);

        // VIES must not be called — the user explicitly chose EIK/registration lookup
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'ec.europa.eu'));

        // Gemini must receive the bare number without any country prefix
        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), 'generativelanguage')) {
                return false;
            }

            $body = json_decode($request->body(), true);
            $prompt = $body['contents'][0]['parts'][0]['text'] ?? '';

            return str_contains($prompt, 'registration number 203137077')
                && ! str_starts_with($prompt, 'Look up the company with VAT number BG203137077');
        });
    }

    public function test_detect_input_type_returns_bare_number_when_forced_registration(): void
    {
        // Pure numeric input with forceType='registration' must NOT prepend country code
        $result = $this->service->detectInputType('203137077', 'BG', 'registration');

        $this->assertNotNull($result);
        $this->assertSame('BG', $result['country']);
        $this->assertSame('203137077', $result['number']); // bare number, no "BG" prefix
        $this->assertSame('registration', $result['type']);
    }

    public function test_detect_input_type_strips_prefix_when_forced_registration_with_country_prefix(): void
    {
        // Input "BG203137077" with forceType='registration' must return the bare number
        $result = $this->service->detectInputType('BG203137077', 'BG', 'registration');

        $this->assertNotNull($result);
        $this->assertSame('BG', $result['country']);
        $this->assertSame('203137077', $result['number']); // prefix stripped
        $this->assertSame('registration', $result['type']);
    }

    public function test_gemini_country_is_overridden_by_searched_country(): void
    {
        // When Gemini returns a wrong country (e.g. AT instead of BG), the
        // output must always use the country we searched in.
        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => json_encode([
                        'found' => true,
                        'name' => 'Wrong Country Corp',
                        'address' => 'Vienna',
                        'country' => 'AT',
                        'vat_number' => null,
                        'registration_number' => '203137077',
                        'vat_registered' => false,
                    ])]]],
                ]],
            ], 200),
        ]);

        $result = $this->service->lookupGemini('BG', '203137077', 'registration');

        $this->assertTrue($result['found']);
        $this->assertSame('BG', $result['country']);
    }

    public function test_eik_registration_and_vat_lookups_use_separate_cache_keys(): void
    {
        // When the same number is looked up as EIK (registration) and as VAT,
        // they must use different cache keys so results don't cross-contaminate.
        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push([
                    'candidates' => [[
                        'content' => ['parts' => [['text' => json_encode([
                            'found' => true,
                            'name' => 'EIK Company',
                            'address' => 'Addr 1',
                            'country' => 'BG',
                            'vat_number' => null,
                            'registration_number' => '203137077',
                            'vat_registered' => false,
                        ])]]],
                    ]],
                ])
                ->push([
                    'candidates' => [[
                        'content' => ['parts' => [['text' => json_encode([
                            'found' => true,
                            'name' => 'VAT Company',
                            'address' => 'Addr 2',
                            'country' => 'BG',
                            'vat_number' => 'BG203137077',
                            'registration_number' => null,
                            'vat_registered' => true,
                        ])]]],
                    ]],
                ]),
        ]);

        // EIK lookup — number is bare "203137077"
        $eikResult = $this->service->lookupGemini('BG', '203137077', 'registration');
        // VAT lookup — number includes prefix "BG203137077"
        $vatResult = $this->service->lookupGemini('BG', 'BG203137077', 'vat');

        // Both must hit Gemini — different cache keys
        Http::assertSentCount(2);
        $this->assertSame('EIK Company', $eikResult['name']);
        $this->assertSame('VAT Company', $vatResult['name']);
    }

    public function test_cache_key_separates_vat_and_registration_lookups(): void
    {
        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push([
                    'candidates' => [[
                        'content' => ['parts' => [['text' => json_encode([
                            'found' => true,
                            'name' => 'VAT Company',
                            'address' => 'Addr 1',
                            'country' => 'BG',
                            'vat_number' => 'BG203137077',
                            'registration_number' => null,
                            'vat_registered' => true,
                        ])]]],
                    ]],
                ])
                ->push([
                    'candidates' => [[
                        'content' => ['parts' => [['text' => json_encode([
                            'found' => true,
                            'name' => 'Registration Company',
                            'address' => 'Addr 2',
                            'country' => 'BG',
                            'vat_number' => null,
                            'registration_number' => '203137077',
                            'vat_registered' => false,
                        ])]]],
                    ]],
                ]),
        ]);

        $vatResult = $this->service->lookupGemini('BG', '203137077', 'vat');
        $regResult = $this->service->lookupGemini('BG', '203137077', 'registration');

        // Both must hit Gemini — different cache keys
        Http::assertSentCount(2);
        $this->assertSame('VAT Company', $vatResult['name']);
        $this->assertSame('Registration Company', $regResult['name']);
    }
}
