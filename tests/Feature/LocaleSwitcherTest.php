<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_to_a_supported_locale_stores_it_in_session(): void
    {
        $this->post(route('locale.switch'), ['locale' => 'bg'])
            ->assertRedirect()
            ->assertSessionHas('locale', 'bg');
    }

    public function test_switching_to_an_unsupported_locale_is_ignored(): void
    {
        $this->post(route('locale.switch'), ['locale' => 'xx'])
            ->assertRedirect()
            ->assertSessionMissing('locale');
    }

    public function test_locale_middleware_sets_app_locale_from_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['locale' => 'bg'])
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertEquals('bg', app()->getLocale());
    }

    public function test_language_switcher_is_visible_in_app_layout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('locale.switch'));
    }

    public function test_user_db_locale_takes_priority_over_session(): void
    {
        $user = User::factory()->create(['locale' => 'fr']);

        $this->actingAs($user)
            ->withSession(['locale' => 'bg'])
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertEquals('fr', app()->getLocale());
    }

    public function test_session_locale_is_used_when_user_has_no_db_locale(): void
    {
        $user = User::factory()->create(['locale' => null]);

        $this->actingAs($user)
            ->withSession(['locale' => 'de'])
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertEquals('de', app()->getLocale());
    }
}
