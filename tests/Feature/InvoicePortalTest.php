<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceAccessToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_token_renders_portal(): void
    {
        $invoice = Invoice::factory()->sent()->create();
        $token = $invoice->generatePortalLink();

        $this->get(route('invoice.portal', $token->token))
            ->assertOk()
            ->assertViewIs('invoices.portal');
    }

    public function test_invalid_token_returns_404(): void
    {
        $this->get(route('invoice.portal', 'nonexistent-token'))
            ->assertNotFound();
    }

    public function test_expired_token_returns_410(): void
    {
        $invoice = Invoice::factory()->sent()->create();
        $token = $invoice->generatePortalLink(null, now()->subDay());

        $this->get(route('invoice.portal', $token->token))
            ->assertStatus(410);
    }

    public function test_password_protected_token_shows_auth_form(): void
    {
        $invoice = Invoice::factory()->sent()->create();
        $token = $invoice->generatePortalLink('secret123');

        $this->get(route('invoice.portal', $token->token))
            ->assertOk()
            ->assertViewIs('invoices.portal-auth');
    }

    public function test_correct_password_grants_access(): void
    {
        $invoice = Invoice::factory()->sent()->create();
        $token = $invoice->generatePortalLink('secret123');

        $this->post(route('invoice.portal.auth', $token->token), ['password' => 'secret123'])
            ->assertRedirect(route('invoice.portal', $token->token));

        // Now the session is set; visiting the portal should render the portal view
        $this->withSession(['portal_auth_' . $token->token => true])
            ->get(route('invoice.portal', $token->token))
            ->assertOk()
            ->assertViewIs('invoices.portal');
    }

    public function test_wrong_password_shows_error(): void
    {
        $invoice = Invoice::factory()->sent()->create();
        $token = $invoice->generatePortalLink('secret123');

        $this->post(route('invoice.portal.auth', $token->token), ['password' => 'wrongpassword'])
            ->assertRedirect()
            ->assertSessionHasErrors('password');
    }

    public function test_portal_records_accessed_at(): void
    {
        $invoice = Invoice::factory()->sent()->create();
        $token = $invoice->generatePortalLink();

        $this->assertNull($token->accessed_at);

        $this->get(route('invoice.portal', $token->token))->assertOk();

        $this->assertNotNull($token->fresh()->accessed_at);
    }

    public function test_generate_portal_link_route_requires_auth(): void
    {
        $invoice = Invoice::factory()->create();

        $this->post(route('invoices.portal-link', $invoice))
            ->assertRedirect(route('login'));
    }

    public function test_generate_portal_link_creates_token(): void
    {
        $invoice = Invoice::factory()->create();

        $this->actingAs($invoice->user)
            ->post(route('invoices.portal-link', $invoice))
            ->assertRedirect()
            ->assertSessionHas('portal_url');

        $this->assertDatabaseCount('invoice_access_tokens', 1);
    }

    public function test_generate_portal_link_enforces_ownership(): void
    {
        $invoice = Invoice::factory()->create();
        $otherUser = \App\Models\User::factory()->create();

        $this->actingAs($otherUser)
            ->post(route('invoices.portal-link', $invoice))
            ->assertForbidden();
    }

    public function test_invoice_access_token_is_expired(): void
    {
        $invoice = Invoice::factory()->create();
        $token = InvoiceAccessToken::create([
            'invoice_id' => $invoice->id,
            'token' => 'test-token-123',
            'password_hash' => null,
            'expires_at' => now()->subHour(),
        ]);

        $this->assertTrue($token->isExpired());
    }

    public function test_invoice_access_token_not_expired_without_expiry(): void
    {
        $invoice = Invoice::factory()->create();
        $token = InvoiceAccessToken::create([
            'invoice_id' => $invoice->id,
            'token' => 'test-token-456',
            'password_hash' => null,
            'expires_at' => null,
        ]);

        $this->assertFalse($token->isExpired());
    }

    public function test_download_query_param_returns_pdf(): void
    {
        $invoice = Invoice::factory()->sent()->create();
        $token = $invoice->generatePortalLink();

        $response = $this->get(route('invoice.portal', $token->token) . '?download=1');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_download_not_accessible_with_expired_token(): void
    {
        $invoice = Invoice::factory()->sent()->create();
        $token = $invoice->generatePortalLink(null, now()->subDay());

        $this->get(route('invoice.portal', $token->token) . '?download=1')
            ->assertStatus(410);
    }
}
