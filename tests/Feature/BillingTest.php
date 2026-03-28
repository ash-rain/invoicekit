<?php

namespace Tests\Feature;

use App\Mail\PaymentFailedNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    // ── Auth guards ───────────────────────────────────────────────────────────

    public function test_billing_page_requires_auth(): void
    {
        $this->get(route('billing.index'))
            ->assertRedirect(route('login'));
    }

    public function test_checkout_requires_auth(): void
    {
        $this->post(route('billing.checkout', 'pro'))
            ->assertRedirect(route('login'));
    }

    public function test_portal_requires_auth(): void
    {
        $this->post(route('billing.portal'))
            ->assertRedirect(route('login'));
    }

    // ── Billing page ──────────────────────────────────────────────────────────

    public function test_billing_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Billing & Subscription');
    }

    public function test_billing_page_shows_trial_banner_for_trial_user(): void
    {
        $user = User::factory()->create([
            'plan' => 'pro',
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Free trial active');
    }

    public function test_billing_page_does_not_show_trial_banner_when_trial_expired(): void
    {
        $user = User::factory()->create([
            'plan' => 'free',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertDontSee('Free trial active');
    }

    public function test_billing_page_shows_subscription_status_active_badge(): void
    {
        $user = User::factory()->create([
            'plan' => 'pro',
            'subscription_status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Active');
    }

    public function test_billing_page_shows_overdue_badge(): void
    {
        $user = User::factory()->create([
            'plan' => 'pro',
            'subscription_status' => 'past_due',
        ]);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Payment overdue');
    }

    // ── Checkout ──────────────────────────────────────────────────────────────

    public function test_checkout_with_invalid_plan_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('billing.checkout', 'invalid'))
            ->assertNotFound();
    }

    public function test_checkout_without_stripe_configured_returns_error(): void
    {
        config(['services.stripe.key' => null]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('billing.checkout', 'pro'))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── Portal ────────────────────────────────────────────────────────────────

    public function test_portal_without_stripe_configured_returns_error(): void
    {
        config(['services.stripe.key' => null]);

        $user = User::factory()->create(['plan' => 'pro']);

        $this->actingAs($user)
            ->post(route('billing.portal'))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_portal_without_stripe_customer_returns_error(): void
    {
        config(['services.stripe.key' => 'sk_test_fake']);

        $user = User::factory()->create([
            'plan' => 'pro',
            'stripe_customer_id' => null,
        ]);

        $this->actingAs($user)
            ->post(route('billing.portal'))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── Webhook ───────────────────────────────────────────────────────────────

    public function test_webhook_endpoint_is_accessible_without_auth_or_csrf(): void
    {
        // No CSRF / no auth — should not 401 or 419
        $response = $this->postJson(route('billing.webhook'), [
            'type' => 'unknown.event',
            'data' => ['object' => []],
        ]);

        // Without a signature secret configured it will process and return OK
        $response->assertOk();
    }

    public function test_webhook_handles_checkout_session_completed(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_test123',
            'plan' => 'free',
        ]);

        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'customer' => 'cus_test123',
                    'subscription' => 'sub_test456',
                ],
            ],
        ];

        $this->postJson(route('billing.webhook'), $payload)->assertOk();

        $user->refresh();
        $this->assertEquals('pro', $user->plan);
        $this->assertEquals('active', $user->subscription_status);
        $this->assertEquals('sub_test456', $user->stripe_subscription_id);
    }

    public function test_webhook_handles_subscription_updated(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_upd789',
            'plan' => 'free',
        ]);

        $futureTimestamp = Carbon::now()->addMonth()->timestamp;

        $payload = [
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'customer' => 'cus_upd789',
                    'status' => 'active',
                    'current_period_end' => $futureTimestamp,
                ],
            ],
        ];

        $this->postJson(route('billing.webhook'), $payload)->assertOk();

        $user->refresh();
        $this->assertEquals('active', $user->subscription_status);
        $this->assertEquals('pro', $user->plan);
        $this->assertNotNull($user->subscribed_until);
    }

    public function test_webhook_handles_subscription_deleted(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_del321',
            'plan' => 'pro',
            'subscription_status' => 'active',
            'stripe_subscription_id' => 'sub_del999',
        ]);

        $payload = [
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'customer' => 'cus_del321',
                ],
            ],
        ];

        $this->postJson(route('billing.webhook'), $payload)->assertOk();

        $user->refresh();
        $this->assertEquals('free', $user->plan);
        $this->assertEquals('canceled', $user->subscription_status);
        $this->assertNull($user->stripe_subscription_id);
    }

    public function test_webhook_handles_payment_failed(): void
    {
        Mail::fake();
        config(['services.stripe.webhook_secret' => null]);

        $user = User::factory()->create([
            'stripe_customer_id' => 'cus_fail777',
            'plan' => 'pro',
            'subscription_status' => 'active',
        ]);

        $payload = [
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'customer' => 'cus_fail777',
                ],
            ],
        ];

        $this->postJson(route('billing.webhook'), $payload)->assertOk();

        $user->refresh();
        $this->assertEquals('past_due', $user->subscription_status);
        Mail::assertSent(PaymentFailedNotification::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_webhook_with_unknown_customer_does_not_error(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'customer' => 'cus_nobody',
                    'subscription' => 'sub_nobody',
                ],
            ],
        ];

        $this->postJson(route('billing.webhook'), $payload)->assertOk();
    }

    // ── User model helpers ────────────────────────────────────────────────────

    public function test_is_on_trial_returns_true_within_trial_period(): void
    {
        $user = User::factory()->make(['trial_ends_at' => now()->addDays(5)]);

        $this->assertTrue($user->isOnTrial());
    }

    public function test_is_on_trial_returns_false_after_trial_expires(): void
    {
        $user = User::factory()->make(['trial_ends_at' => now()->subDay()]);

        $this->assertFalse($user->isOnTrial());
    }

    public function test_is_on_trial_returns_false_when_null(): void
    {
        $user = User::factory()->make(['trial_ends_at' => null]);

        $this->assertFalse($user->isOnTrial());
    }

    public function test_has_active_subscription_when_status_is_active(): void
    {
        $user = User::factory()->make(['subscription_status' => 'active']);

        $this->assertTrue($user->hasActiveSubscription());
    }

    public function test_has_active_subscription_when_subscribed_until_is_future(): void
    {
        $user = User::factory()->make([
            'subscription_status' => null,
            'subscribed_until' => now()->addDays(30),
        ]);

        $this->assertTrue($user->hasActiveSubscription());
    }

    public function test_has_active_subscription_returns_false_when_canceled(): void
    {
        $user = User::factory()->make([
            'subscription_status' => 'canceled',
            'subscribed_until' => null,
        ]);

        $this->assertFalse($user->hasActiveSubscription());
    }

    // ── Registration trial ────────────────────────────────────────────────────

    public function test_new_user_gets_14_day_pro_trial_on_registration(): void
    {
        $this->post(route('register'), [
            'name' => 'Trial User',
            'email' => 'trial@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('onboarding'));

        $user = User::where('email', 'trial@example.com')->firstOrFail();

        $this->assertEquals('pro', $user->plan);
        $this->assertNotNull($user->trial_ends_at);
        $this->assertTrue($user->trial_ends_at->isFuture());
        $this->assertEqualsWithDelta(14, now()->diffInDays($user->trial_ends_at), 1);
    }
}
