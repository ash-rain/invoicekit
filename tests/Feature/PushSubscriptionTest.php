<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_PAYLOAD = [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'keys' => [
            'p256dh' => 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA_0QHTd_9e7gMr-XpVHnTBZmMNY3GEQrFSmONh5Xl3qhRRB0',
            'auth' => 'tBHItJI5svbpez7KI4CCXg',
        ],
    ];

    // ── Authentication ────────────────────────────────────────────────

    public function test_guests_cannot_subscribe(): void
    {
        $this->postJson(route('push-subscriptions.store'), self::VALID_PAYLOAD)
            ->assertUnauthorized();
    }

    public function test_guests_cannot_unsubscribe(): void
    {
        $this->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => self::VALID_PAYLOAD['endpoint']])
            ->assertUnauthorized();
    }

    // ── Store ────────────────────────────────────────────────────────

    public function test_authenticated_user_can_store_subscription(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), self::VALID_PAYLOAD)
            ->assertNoContent();

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $user->id,
            'endpoint' => self::VALID_PAYLOAD['endpoint'],
        ]);
    }

    public function test_store_requires_endpoint(): void
    {
        $user = User::factory()->create();
        $payload = self::VALID_PAYLOAD;
        unset($payload['endpoint']);

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['endpoint']);
    }

    public function test_store_requires_valid_url_endpoint(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), array_merge(self::VALID_PAYLOAD, ['endpoint' => 'not-a-url']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['endpoint']);
    }

    public function test_store_requires_auth_key(): void
    {
        $user = User::factory()->create();
        $payload = self::VALID_PAYLOAD;
        unset($payload['keys']['auth']);

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['keys.auth']);
    }

    public function test_store_requires_p256dh_key(): void
    {
        $user = User::factory()->create();
        $payload = self::VALID_PAYLOAD;
        unset($payload['keys']['p256dh']);

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['keys.p256dh']);
    }

    // ── Destroy ──────────────────────────────────────────────────────

    public function test_authenticated_user_can_delete_subscription(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), self::VALID_PAYLOAD)
            ->assertNoContent();

        $this->actingAs($user)
            ->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => self::VALID_PAYLOAD['endpoint']])
            ->assertNoContent();

        $this->assertDatabaseMissing('push_subscriptions', [
            'subscribable_id' => $user->id,
            'endpoint' => self::VALID_PAYLOAD['endpoint'],
        ]);
    }

    public function test_destroy_requires_endpoint(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('push-subscriptions.destroy'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['endpoint']);
    }
}
