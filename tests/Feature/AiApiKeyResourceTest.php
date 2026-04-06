<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AiApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiApiKeyResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_api_keys_list_requires_admin_auth(): void
    {
        $this->get('/admin/ai-api-keys')
            ->assertRedirect('/admin/login');
    }

    public function test_web_user_cannot_access_ai_api_keys(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get('/admin/ai-api-keys')
            ->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_ai_api_keys_list(): void
    {
        $admin = Admin::factory()->create();
        AiApiKey::factory()->count(3)->create();

        $this->actingAs($admin, 'admin')
            ->get('/admin/ai-api-keys')
            ->assertOk();
    }

    public function test_admin_can_access_create_ai_api_key_page(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('/admin/ai-api-keys/create')
            ->assertOk();
    }

    public function test_admin_can_access_edit_ai_api_key_page(): void
    {
        $admin = Admin::factory()->create();
        $key = AiApiKey::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get("/admin/ai-api-keys/{$key->id}/edit")
            ->assertOk();
    }

    public function test_ai_api_key_model_encrypts_api_key(): void
    {
        $key = AiApiKey::factory()->create(['api_key' => 'AIzaTestKey123456789']);

        // The stored value in DB should be encrypted (not the plain text)
        $raw = \DB::table('ai_api_keys')->where('id', $key->id)->value('api_key');
        $this->assertNotEquals('AIzaTestKey123456789', $raw);

        // But through the model it should decrypt
        $this->assertEquals('AIzaTestKey123456789', $key->api_key);
    }

    public function test_ai_api_key_available_scope_excludes_inactive_keys(): void
    {
        AiApiKey::factory()->inactive()->create();
        $active = AiApiKey::factory()->available()->create();

        $available = AiApiKey::available()->get();

        $this->assertCount(1, $available);
        $this->assertEquals($active->id, $available->first()->id);
    }

    public function test_ai_api_key_available_scope_excludes_errored_keys_in_cooldown(): void
    {
        AiApiKey::factory()->withError()->create(['is_active' => true]);
        $clean = AiApiKey::factory()->available()->create();

        $available = AiApiKey::available()->get();

        $this->assertCount(1, $available);
        $this->assertEquals($clean->id, $available->first()->id);
    }

    public function test_ai_api_key_available_scope_includes_key_after_cooldown(): void
    {
        AiApiKey::factory()->create([
            'is_active' => true,
            'last_error_at' => now()->subSeconds(61),
        ]);

        $this->assertCount(1, AiApiKey::available()->get());
    }

    public function test_ai_api_key_is_active_by_default(): void
    {
        $key = AiApiKey::factory()->create();

        $this->assertTrue($key->is_active);
    }

    public function test_ai_api_key_inactive_state_sets_is_active_false(): void
    {
        $key = AiApiKey::factory()->inactive()->create();

        $this->assertFalse($key->is_active);
        $this->assertCount(0, AiApiKey::available()->get());
    }
}
