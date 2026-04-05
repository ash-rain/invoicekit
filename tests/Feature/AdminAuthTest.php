<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_loads(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
    }

    public function test_unauthenticated_user_is_redirected_from_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect();
    }

    public function test_web_user_cannot_access_admin_panel(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user, 'web')->get('/admin');

        $response->assertRedirect();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get('/admin');

        $response->assertOk();
    }

    public function test_admin_blog_posts_page_is_accessible(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get('/admin/blog-posts');

        $response->assertOk();
    }

    public function test_admin_users_page_is_accessible(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get('/admin/users');

        $response->assertOk();
    }

    public function test_admin_profile_page_is_accessible(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get('/admin/profile');

        $response->assertOk();
    }
}
