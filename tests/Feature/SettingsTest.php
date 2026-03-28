<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_is_accessible(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk();
    }

    public function test_guests_cannot_access_settings(): void
    {
        $this->get(route('settings.index'))
            ->assertRedirect(route('login'));
    }

    public function test_profile_route_redirects_to_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile')
            ->assertRedirect(route('settings.index'));
    }

    public function test_profile_tab_saves_user_data(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->set('name', 'Jane Doe')
            ->set('displayName', 'Jane')
            ->set('tagline', 'Full-stack Developer')
            ->set('website', 'https://jane.dev')
            ->set('phone', '+49 123 456789')
            ->call('saveProfile');

        $user->refresh();

        $this->assertSame('Jane Doe', $user->name);
        $this->assertSame('Jane', $user->display_name);
        $this->assertSame('Full-stack Developer', $user->tagline);
        $this->assertSame('https://jane.dev', $user->website);
        $this->assertSame('+49 123 456789', $user->phone);
    }

    public function test_business_tab_creates_company_if_none_exists(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->current_company_id);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->set('companyName', 'My GmbH')
            ->set('companyCountry', 'DE')
            ->set('vatNumber', 'DE123456789')
            ->call('saveBusiness');

        $user->refresh();

        $this->assertNotNull($user->current_company_id);
        $this->assertDatabaseHas('companies', [
            'user_id' => $user->id,
            'name' => 'My GmbH',
            'country' => 'DE',
            'vat_number' => 'DE123456789',
        ]);
    }

    public function test_business_tab_updates_existing_company(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);
        $user->update(['current_company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->set('companyName', 'Updated Corp')
            ->set('companyCountry', 'FR')
            ->call('saveBusiness');

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Updated Corp',
            'country' => 'FR',
        ]);
    }

    public function test_invoicing_tab_enables_vat_exemption(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'user_id' => $user->id,
            'country' => 'DE',
            'vat_exempt' => false,
        ]);
        $user->update(['current_company_id' => $company->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->set('vatExempt', true)
            ->set('vatExemptReason', 'Revenue below §19 UStG threshold')
            ->set('vatExemptNoticeLanguage', 'en')
            ->call('saveInvoicing');

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'vat_exempt' => true,
            'vat_exempt_notice_language' => 'en',
        ]);
    }

    public function test_profile_validation_requires_name(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->set('name', '')
            ->call('saveProfile')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_business_validation_requires_company_name_and_country(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings::class)
            ->set('companyName', '')
            ->set('companyCountry', '')
            ->call('saveBusiness')
            ->assertHasErrors(['companyName', 'companyCountry']);
    }
}
