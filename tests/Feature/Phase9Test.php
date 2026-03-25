<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase9Test extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────
    // Landing page
    // ──────────────────────────────────────────────────────────────────

    public function test_landing_page_is_accessible(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_landing_page_redirects_authenticated_users_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }

    // ──────────────────────────────────────────────────────────────────
    // Legal pages
    // ──────────────────────────────────────────────────────────────────

    public function test_privacy_policy_page_is_accessible(): void
    {
        $this->get(route('privacy'))->assertOk();
    }

    public function test_terms_of_service_page_is_accessible(): void
    {
        $this->get(route('terms'))->assertOk();
    }

    public function test_privacy_page_contains_gdpr_content(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('GDPR');
    }

    public function test_terms_page_contains_subscription_info(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Subscription');
    }

    // ──────────────────────────────────────────────────────────────────
    // SEO: sitemap
    // ──────────────────────────────────────────────────────────────────

    public function test_sitemap_is_accessible(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_contains_public_urls(): void
    {
        $response = $this->get(route('sitemap'))->assertOk();

        $this->assertStringContainsString('<urlset', $response->getContent());
        $this->assertStringContainsString(url('/'), $response->getContent());
    }

    // ──────────────────────────────────────────────────────────────────
    // SEO: robots.txt
    // ──────────────────────────────────────────────────────────────────

    public function test_robots_txt_route_is_accessible(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function test_robots_txt_disallows_private_routes(): void
    {
        $response = $this->get(route('robots'))->assertOk();

        $this->assertStringContainsString('Disallow: /dashboard', $response->getContent());
        $this->assertStringContainsString('Disallow: /invoices', $response->getContent());
        $this->assertStringContainsString('Sitemap:', $response->getContent());
    }

    public function test_robots_txt_sitemap_contains_absolute_url(): void
    {
        $response = $this->get(route('robots'))->assertOk();

        $this->assertStringContainsString('Sitemap: http', $response->getContent());
    }

    // ──────────────────────────────────────────────────────────────────
    // Error pages
    // ──────────────────────────────────────────────────────────────────

    public function test_404_page_is_returned_for_unknown_routes(): void
    {
        $this->get('/nonexistent-page-xyz')
            ->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────────────
    // Onboarding
    // ──────────────────────────────────────────────────────────────────

    public function test_new_user_is_redirected_to_onboarding_after_registration(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('onboarding'));
    }

    public function test_onboarding_page_is_accessible_for_authenticated_user(): void
    {
        $user = User::factory()->create(['onboarding_completed' => false]);

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertOk();
    }

    public function test_completed_onboarding_user_is_redirected_from_onboarding(): void
    {
        $user = User::factory()->create(['onboarding_completed' => true]);

        // Component redirects if onboarding is completed
        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\OnboardingWizard::class)
            ->assertRedirect(route('dashboard'));
    }

    // ──────────────────────────────────────────────────────────────────
    // Billing
    // ──────────────────────────────────────────────────────────────────

    public function test_billing_page_is_accessible_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk();
    }

    public function test_billing_page_shows_current_plan(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertSee('Free');
    }

    public function test_billing_page_shows_upgrade_options_for_free_plan(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertSee('Upgrade')
            ->assertSee('€29');
    }

    // ──────────────────────────────────────────────────────────────────
    // PlanService
    // ──────────────────────────────────────────────────────────────────

    public function test_plan_service_free_plan_client_limit(): void
    {
        $planService = app(PlanService::class);
        $user = User::factory()->create(['plan' => 'free']);

        $this->assertEquals(3, $planService->getPlan($user)['clients_limit']);
    }

    public function test_plan_service_pro_plan_has_no_client_limit(): void
    {
        $planService = app(PlanService::class);
        $user = User::factory()->create(['plan' => 'pro']);

        $this->assertNull($planService->getPlan($user)['clients_limit']);
    }

    public function test_plan_service_free_plan_invoice_limit(): void
    {
        $planService = app(PlanService::class);
        $user = User::factory()->create(['plan' => 'free']);

        $this->assertEquals(5, $planService->getPlan($user)['invoices_per_month_limit']);
    }

    public function test_plan_service_pro_plan_has_no_invoice_limit(): void
    {
        $planService = app(PlanService::class);
        $user = User::factory()->create(['plan' => 'pro']);

        $this->assertNull($planService->getPlan($user)['invoices_per_month_limit']);
    }

    public function test_is_pro_helper_returns_true_for_pro_users(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);

        $this->assertTrue($user->isPro());
        $this->assertFalse($user->isFree());
    }

    public function test_is_free_helper_returns_true_for_free_users(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->assertTrue($user->isFree());
        $this->assertFalse($user->isPro());
    }

    public function test_is_starter_returns_true_for_starter_and_pro(): void
    {
        $starter = User::factory()->create(['plan' => 'starter']);
        $pro = User::factory()->create(['plan' => 'pro']);
        $free = User::factory()->create(['plan' => 'free']);

        $this->assertTrue($starter->isStarter());
        $this->assertTrue($pro->isStarter());
        $this->assertFalse($free->isStarter());
    }
}
