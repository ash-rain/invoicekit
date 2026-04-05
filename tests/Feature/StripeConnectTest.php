<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeConnectTest extends TestCase
{
    use RefreshDatabase;

    // ── Auth guards ───────────────────────────────────────────────────────────

    public function test_onboard_requires_auth(): void
    {
        $this->post(route('stripe-connect.onboard'))
            ->assertRedirect(route('login'));
    }

    public function test_callback_requires_auth(): void
    {
        $this->get(route('stripe-connect.callback'))
            ->assertRedirect(route('login'));
    }

    public function test_refresh_requires_auth(): void
    {
        $this->get(route('stripe-connect.refresh'))
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_requires_auth(): void
    {
        $this->get(route('stripe-connect.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_disconnect_requires_auth(): void
    {
        $this->post(route('stripe-connect.disconnect'))
            ->assertRedirect(route('login'));
    }

    // ── hasStripeConnect() helper ─────────────────────────────────────────────

    public function test_has_stripe_connect_returns_false_when_no_account(): void
    {
        $user = User::factory()->create([
            'stripe_connect_id' => null,
            'stripe_connect_onboarded' => false,
        ]);

        $this->assertFalse($user->hasStripeConnect());
    }

    public function test_has_stripe_connect_returns_false_when_not_onboarded(): void
    {
        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_test123',
            'stripe_connect_onboarded' => false,
        ]);

        $this->assertFalse($user->hasStripeConnect());
    }

    public function test_has_stripe_connect_returns_true_when_onboarded(): void
    {
        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_test123',
            'stripe_connect_onboarded' => true,
        ]);

        $this->assertTrue($user->hasStripeConnect());
    }

    // ── Callback ──────────────────────────────────────────────────────────────

    public function test_callback_redirects_to_settings_when_no_connect_id(): void
    {
        $user = User::factory()->create(['stripe_connect_id' => null]);

        $this->actingAs($user)
            ->get(route('stripe-connect.callback'))
            ->assertRedirect(route('settings.index', ['tab' => 'payments']));
    }

    // ── Refresh ───────────────────────────────────────────────────────────────

    public function test_refresh_redirects_to_settings_when_no_connect_id(): void
    {
        $user = User::factory()->create(['stripe_connect_id' => null]);

        $this->actingAs($user)
            ->get(route('stripe-connect.refresh'))
            ->assertRedirect(route('settings.index', ['tab' => 'payments']));
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function test_dashboard_redirects_back_when_not_connected(): void
    {
        $user = User::factory()->create([
            'stripe_connect_id' => null,
            'stripe_connect_onboarded' => false,
        ]);

        $this->actingAs($user)
            ->get(route('stripe-connect.dashboard'))
            ->assertRedirect();
    }

    // ── Disconnect ────────────────────────────────────────────────────────────

    public function test_disconnect_redirects_to_settings_when_no_connect_id(): void
    {
        $user = User::factory()->create(['stripe_connect_id' => null]);

        $this->actingAs($user)
            ->post(route('stripe-connect.disconnect'))
            ->assertRedirect(route('settings.index', ['tab' => 'payments']));
    }

    public function test_disconnect_clears_connect_fields(): void
    {
        config(['services.stripe.key' => null]); // skip Stripe API call

        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_test123',
            'stripe_connect_onboarded' => true,
        ]);

        $this->actingAs($user)
            ->post(route('stripe-connect.disconnect'))
            ->assertRedirect(route('settings.index', ['tab' => 'payments']));

        $user->refresh();
        $this->assertNull($user->stripe_connect_id);
        $this->assertFalse($user->stripe_connect_onboarded);
    }

    // ── Payment link (Checkout Session) ──────────────────────────────────────

    public function test_create_payment_link_requires_stripe_connect(): void
    {
        $user = User::factory()->create([
            'stripe_connect_id' => null,
            'stripe_connect_onboarded' => false,
        ]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'sent']);

        $this->actingAs($user)
            ->post(route('invoices.payment-link', $invoice))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_create_payment_link_rejects_paid_invoice(): void
    {
        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_test123',
            'stripe_connect_onboarded' => true,
        ]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'paid']);

        $this->actingAs($user)
            ->post(route('invoices.payment-link', $invoice))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_create_payment_link_rejects_other_users_invoice(): void
    {
        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_test123',
            'stripe_connect_onboarded' => true,
        ]);
        $other = User::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $other->id, 'status' => 'sent']);

        $this->actingAs($user)
            ->post(route('invoices.payment-link', $invoice))
            ->assertForbidden();
    }

    // ── Webhook: account.updated ──────────────────────────────────────────────

    public function test_webhook_account_updated_marks_user_as_onboarded(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_webhook123',
            'stripe_connect_onboarded' => false,
        ]);

        $this->postJson(route('billing.webhook'), [
            'type' => 'account.updated',
            'data' => [
                'object' => [
                    'id' => 'acct_webhook123',
                    'charges_enabled' => true,
                    'details_submitted' => true,
                ],
            ],
        ])->assertOk();

        $user->refresh();
        $this->assertTrue($user->stripe_connect_onboarded);
    }

    public function test_webhook_account_updated_disables_previously_onboarded_account(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_webhook456',
            'stripe_connect_onboarded' => true,
        ]);

        $this->postJson(route('billing.webhook'), [
            'type' => 'account.updated',
            'data' => [
                'object' => [
                    'id' => 'acct_webhook456',
                    'charges_enabled' => false,
                    'details_submitted' => true,
                ],
            ],
        ])->assertOk();

        $user->refresh();
        $this->assertFalse($user->stripe_connect_onboarded);
    }

    // ── Webhook: checkout.session.completed (invoice type) ───────────────────

    public function test_webhook_checkout_completed_marks_invoice_as_paid(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $user = User::factory()->create([
            'stripe_connect_id' => 'acct_test123',
            'stripe_connect_onboarded' => true,
        ]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'sent']);

        $this->postJson(route('billing.webhook'), [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'metadata' => [
                        'type' => 'invoice_payment',
                        'invoice_id' => $invoice->id,
                    ],
                ],
            ],
        ])->assertOk();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_webhook_checkout_completed_does_not_double_mark_paid_invoice(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $user = User::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'paid_at' => now()->subDay(),
        ]);
        $originalPaidAt = $invoice->paid_at->toDateString();

        $this->postJson(route('billing.webhook'), [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'metadata' => [
                        'type' => 'invoice_payment',
                        'invoice_id' => $invoice->id,
                    ],
                ],
            ],
        ])->assertOk();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame($originalPaidAt, $invoice->paid_at->toDateString());
    }
}
