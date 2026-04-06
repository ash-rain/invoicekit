<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GeminiExtractionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class UserApiKeyTest extends TestCase
{
    use RefreshDatabase;

    // ------ saveAi ------

    public function test_save_ai_key_validates_minimum_length(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->set('geminiApiKey', 'short')
            ->call('saveAi')
            ->assertHasErrors('geminiApiKey');
    }

    public function test_save_ai_key_calls_test_raw_key_and_persists(): void
    {
        $user = User::factory()->create();

        $gemini = Mockery::mock(GeminiExtractionService::class);
        $gemini->shouldReceive('testRawKey')->once()->with('valid-api-key-1234567890')->andReturnNull();
        $this->app->instance(GeminiExtractionService::class, $gemini);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->set('geminiApiKey', 'valid-api-key-1234567890')
            ->call('saveAi')
            ->assertHasNoErrors()
            ->assertSet('geminiApiKey', '');

        $this->assertNotNull($user->fresh()->gemini_api_key);
    }

    public function test_save_ai_key_adds_error_when_key_is_invalid(): void
    {
        $user = User::factory()->create();

        $gemini = Mockery::mock(GeminiExtractionService::class);
        $gemini->shouldReceive('testRawKey')->once()->andThrow(new \RuntimeException('Invalid API key'));
        $this->app->instance(GeminiExtractionService::class, $gemini);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->set('geminiApiKey', 'invalid-api-key-00000000000')
            ->call('saveAi')
            ->assertHasErrors('geminiApiKey');

        $this->assertNull($user->fresh()->gemini_api_key);
    }

    public function test_save_ai_key_resets_form_field_on_success(): void
    {
        $user = User::factory()->create();

        $gemini = Mockery::mock(GeminiExtractionService::class);
        $gemini->shouldReceive('testRawKey')->once()->andReturnNull();
        $this->app->instance(GeminiExtractionService::class, $gemini);

        // geminiApiKey is cleared after successful save
        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->set('geminiApiKey', 'valid-api-key-1234567890')
            ->call('saveAi')
            ->assertHasNoErrors()
            ->assertSet('geminiApiKey', '');
    }

    // ------ removeGeminiKey ------

    public function test_remove_gemini_key_clears_the_stored_key(): void
    {
        $user = User::factory()->withGeminiKey('my-gemini-key-abcdef12345')->create();

        $this->assertNotNull($user->fresh()->gemini_api_key);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->call('removeGeminiKey');

        $this->assertNull($user->fresh()->gemini_api_key);
    }

    public function test_remove_gemini_key_does_not_leave_errors(): void
    {
        $user = User::factory()->withGeminiKey('my-gemini-key-abcdef12345')->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->call('removeGeminiKey')
            ->assertHasNoErrors();
    }

    // ------ User model encryption ------

    public function test_gemini_api_key_is_stored_encrypted(): void
    {
        $user = User::factory()->create();
        $user->update(['gemini_api_key' => 'plaintext-key-test-1234567890']);

        $raw = \DB::table('users')->where('id', $user->id)->value('gemini_api_key');

        $this->assertNotSame('plaintext-key-test-1234567890', $raw);
        $this->assertSame('plaintext-key-test-1234567890', $user->fresh()->gemini_api_key);
    }
}
