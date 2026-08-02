<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function fakeTurnstilePasses(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $this->fakeTurnstilePasses();
        $user = User::factory()->create(['onboarding_completed' => true]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'test-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_without_completed_onboarding_are_redirected_to_onboarding_after_login(): void
    {
        $this->fakeTurnstilePasses();
        $user = User::factory()->create(['onboarding_completed' => false]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'test-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('onboarding'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $this->fakeTurnstilePasses();
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'cf-turnstile-response' => 'test-token',
        ]);

        $this->assertGuest();
    }

    public function test_login_is_blocked_when_turnstile_verification_fails(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']])]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'bad-token',
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
