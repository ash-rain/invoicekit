<?php

namespace Tests\Unit;

use App\Exceptions\NoAvailableApiKeyException;
use App\Models\AiApiKey;
use App\Services\AiKeyRotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiKeyRotationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AiKeyRotationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AiKeyRotationService;
    }

    public function test_get_next_key_returns_available_key(): void
    {
        $key = AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        $result = $this->service->getNextKey('gemini');

        $this->assertEquals($key->id, $result->id);
    }

    public function test_get_next_key_throws_when_no_keys(): void
    {
        $this->expectException(NoAvailableApiKeyException::class);

        $this->service->getNextKey('gemini');
    }

    public function test_get_next_key_throws_when_all_keys_inactive(): void
    {
        AiApiKey::factory()->inactive()->create(['provider' => 'gemini']);

        $this->expectException(NoAvailableApiKeyException::class);

        $this->service->getNextKey('gemini');
    }

    public function test_get_next_key_skips_keys_in_error_cooldown(): void
    {
        AiApiKey::factory()->withError()->create(['provider' => 'gemini']);

        $this->expectException(NoAvailableApiKeyException::class);

        $this->service->getNextKey('gemini');
    }

    public function test_get_next_key_returns_key_after_cooldown_expires(): void
    {
        $key = AiApiKey::factory()->create([
            'provider' => 'gemini',
            'is_active' => true,
            'last_error_at' => now()->subSeconds(61),
        ]);

        $result = $this->service->getNextKey('gemini');

        $this->assertEquals($key->id, $result->id);
    }

    public function test_get_next_key_filters_by_provider(): void
    {
        AiApiKey::factory()->available()->create(['provider' => 'openai']);
        $geminiKey = AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        $result = $this->service->getNextKey('gemini');

        $this->assertEquals($geminiKey->id, $result->id);
    }

    public function test_mark_used_updates_timestamps_and_count(): void
    {
        $key = AiApiKey::factory()->create([
            'request_count' => 5,
            'last_error_at' => now(),
            'last_error_message' => 'previous error',
        ]);

        $this->service->markUsed($key);

        $key->refresh();
        $this->assertEquals(6, $key->request_count);
        $this->assertNotNull($key->last_used_at);
        $this->assertNull($key->last_error_at);
        $this->assertNull($key->last_error_message);
    }

    public function test_mark_failed_sets_error_info(): void
    {
        $key = AiApiKey::factory()->available()->create();

        $this->service->markFailed($key, 'Rate limit exceeded');

        $key->refresh();
        $this->assertNotNull($key->last_error_at);
        $this->assertEquals('Rate limit exceeded', $key->last_error_message);
    }

    public function test_mark_failed_makes_key_unavailable_within_cooldown(): void
    {
        $key = AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        $this->service->markFailed($key, 'Rate limit exceeded');

        $this->expectException(NoAvailableApiKeyException::class);

        $this->service->getNextKey('gemini');
    }

    public function test_clear_error_resets_error_state(): void
    {
        $key = AiApiKey::factory()->withError('Rate limit exceeded')->create([
            'is_active' => true,
            'provider' => 'gemini',
        ]);

        $this->service->clearError($key);

        $key->refresh();
        $this->assertNull($key->last_error_at);
        $this->assertNull($key->last_error_message);
    }

    public function test_clear_error_makes_key_available_again(): void
    {
        $key = AiApiKey::factory()->withError('Rate limit exceeded')->create([
            'is_active' => true,
            'provider' => 'gemini',
        ]);

        $this->service->clearError($key);

        $result = $this->service->getNextKey('gemini');

        $this->assertEquals($key->id, $result->id);
    }

    public function test_get_next_key_prefers_least_recently_used(): void
    {
        $older = AiApiKey::factory()->create([
            'provider' => 'gemini',
            'is_active' => true,
            'last_error_at' => null,
            'last_used_at' => now()->subHours(2),
        ]);

        $newer = AiApiKey::factory()->create([
            'provider' => 'gemini',
            'is_active' => true,
            'last_error_at' => null,
            'last_used_at' => now()->subMinutes(1),
        ]);

        $result = $this->service->getNextKey('gemini');

        $this->assertEquals($older->id, $result->id);
    }
}
