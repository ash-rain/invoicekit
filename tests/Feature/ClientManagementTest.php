<?php

namespace Tests\Feature;

use App\Models\AiApiKey;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────
    // Access control
    // ──────────────────────────────────────────────────────────────────

    public function test_guests_cannot_access_clients_index(): void
    {
        $this->get(route('clients.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_clients_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('clients.index'))
            ->assertOk();
    }

    public function test_authenticated_users_can_access_create_client_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('clients.create'))
            ->assertOk();
    }

    // ──────────────────────────────────────────────────────────────────
    // Client creation via Livewire component
    // ──────────────────────────────────────────────────────────────────

    public function test_user_can_create_a_client(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('name', 'Acme Corp')
            ->set('email', 'billing@acme.de')
            ->set('country', 'DE')
            ->set('currency', 'EUR')
            ->call('save');

        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'Acme Corp',
            'country' => 'DE',
            'currency' => 'EUR',
        ]);
    }

    public function test_client_default_language_is_saved(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('name', 'Acme Corp')
            ->set('country', 'DE')
            ->set('currency', 'EUR')
            ->set('defaultLanguage', 'de')
            ->call('save');

        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'Acme Corp',
            'default_language' => 'de',
        ]);
    }

    public function test_client_default_language_is_validated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('name', 'Acme Corp')
            ->set('country', 'DE')
            ->set('currency', 'EUR')
            ->set('defaultLanguage', 'xx')
            ->call('save')
            ->assertHasErrors(['defaultLanguage']);
    }

    public function test_client_name_is_required(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_client_country_must_be_two_characters(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('name', 'Test')
            ->set('country', 'DEU')
            ->call('save')
            ->assertHasErrors(['country' => 'size']);
    }

    public function test_client_currency_must_be_in_supported_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('name', 'Test')
            ->set('country', 'DE')
            ->set('currency', 'GBP')
            ->call('save')
            ->assertHasErrors(['currency' => 'in']);
    }

    public function test_invalid_eu_vat_number_format_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('name', 'Acme')
            ->set('country', 'DE')
            ->set('currency', 'EUR')
            ->set('vat_number', 'INVALID123')
            ->call('save')
            ->assertHasErrors(['vat_number']);
    }

    public function test_valid_german_vat_number_is_accepted(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('name', 'Acme GmbH')
            ->set('country', 'DE')
            ->set('currency', 'EUR')
            ->set('vat_number', 'DE123456789')
            ->call('save');

        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'vat_number' => 'DE123456789',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Client list and search
    // ──────────────────────────────────────────────────────────────────

    public function test_client_list_shows_only_own_clients(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Client::factory()->create(['user_id' => $user1->id, 'name' => 'User1 Corp']);
        Client::factory()->create(['user_id' => $user2->id, 'name' => 'User2 Corp']);

        Livewire::actingAs($user1)
            ->test(\App\Livewire\Clients\ClientList::class)
            ->assertSee('User1 Corp')
            ->assertDontSee('User2 Corp');
    }

    public function test_client_list_search_filters_by_name(): void
    {
        $user = User::factory()->create();

        Client::factory()->create(['user_id' => $user->id, 'name' => 'Alpha Company']);
        Client::factory()->create(['user_id' => $user->id, 'name' => 'Beta Company']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\ClientList::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Company')
            ->assertDontSee('Beta Company');
    }

    public function test_user_can_delete_own_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\ClientList::class)
            ->call('deleteClient', $client->id);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_user_cannot_delete_another_users_client(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user2->id]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($user1)
            ->test(\App\Livewire\Clients\ClientList::class)
            ->call('deleteClient', $client->id);
    }

    // ──────────────────────────────────────────────────────────────────
    // Client edit
    // ──────────────────────────────────────────────────────────────────

    public function test_user_can_update_own_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'name' => 'Original Name']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class, ['client' => $client])
            ->set('name', 'Updated Name')
            ->call('save');

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Updated Name']);
    }

    // ──────────────────────────────────────────────────────────────────
    // Plan limit enforcement
    // ──────────────────────────────────────────────────────────────────

    public function test_free_plan_user_cannot_add_more_than_3_clients(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        // Create 3 existing clients (at the limit)
        Client::factory()->count(3)->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('name', 'Fourth Client')
            ->set('country', 'DE')
            ->set('currency', 'EUR')
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertDatabaseMissing('clients', [
            'user_id' => $user->id,
            'name' => 'Fourth Client',
        ]);
    }

    public function test_starter_plan_user_can_add_unlimited_clients(): void
    {
        $user = User::factory()->create(['plan' => 'starter']);

        // Create 3 existing clients (free plan limit)
        Client::factory()->count(3)->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('name', 'Fourth Client')
            ->set('country', 'DE')
            ->set('currency', 'EUR')
            ->call('save');

        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'Fourth Client',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Company lookup — Livewire component integration
    // ──────────────────────────────────────────────────────────────────

    public function test_lookup_populates_form_fields_from_vies(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'ec.europa.eu/*' => Http::response([
                'valid' => true,
                'name' => 'Acme GmbH',
                'address' => 'Musterstraße 1, 10115 Berlin',
            ], 200),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('lookupInput', 'DE123456789')
            ->call('lookupCompany')
            ->assertSet('name', 'Acme GmbH')
            ->assertSet('address', 'Musterstraße 1, 10115 Berlin')
            ->assertSet('country', 'DE')
            ->assertSet('vat_number', 'DE123456789')
            ->assertSet('lookupSource', 'vies')
            ->assertSet('lookupError', '');
    }

    public function test_lookup_shows_error_when_company_not_found(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'ec.europa.eu/*' => Http::response(['valid' => false], 200),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => json_encode(['found' => false])]],
                    ],
                ]],
            ], 200),
        ]);

        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('lookupInput', 'DE000000000')
            ->call('lookupCompany')
            ->assertSet('lookupSource', '')
            ->assertSet('name', ''); // form not populated

        // Verify lookupError is non-empty (exact message tested in unit tests)
        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('lookupInput', 'DE000000000')
            ->call('lookupCompany');

        $this->assertNotEmpty($component->get('lookupError'));
    }

    public function test_lookup_detects_duplicate_client_by_vat_number(): void
    {
        $user = User::factory()->create();

        Client::factory()->create([
            'user_id' => $user->id,
            'vat_number' => 'DE123456789',
        ]);

        Http::fake([
            'ec.europa.eu/*' => Http::response([
                'valid' => true,
                'name' => 'Acme GmbH',
                'address' => 'Berlin',
            ], 200),
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('lookupInput', 'DE123456789')
            ->call('lookupCompany');

        $this->assertNotNull($component->get('existingClientId'));
    }

    public function test_lookup_empty_input_shows_validation_error(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('lookupInput', '')
            ->call('lookupCompany');

        $this->assertNotEmpty($component->get('lookupError'));
        $this->assertSame('', $component->get('lookupSource'));
    }

    public function test_lookup_limit_blocks_free_user_after_cap(): void
    {
        $user = User::factory()->free()->create();

        $limit = config('ai.lookup_limits.free');
        Cache::put(
            'lookup_count:'.$user->id.':'.now()->toDateString(),
            $limit,
            now()->endOfDay()
        );

        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Http::fake([
            'ec.europa.eu/*' => Http::response(['valid' => false], 200),
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('lookupInput', '203137077')
            ->call('lookupCompany');

        $this->assertStringContainsString('limit', strtolower($component->get('lookupError')));
    }

    public function test_registration_number_label_updates_with_country(): void
    {
        $user = User::factory()->create();

        // BG → ЕИК
        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('country', 'BG')
            ->assertSee('ЕИК');

        // NL → KVK-nummer
        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('country', 'NL')
            ->assertSee('KVK-nummer');
    }

    public function test_failed_lookup_does_not_prevent_manual_save(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'ec.europa.eu/*' => Http::response(['valid' => false], 200),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => json_encode(['found' => false])]]],
                ]],
            ], 200),
        ]);

        AiApiKey::factory()->available()->create(['provider' => 'gemini']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\CreateEditClient::class)
            ->set('lookupInput', 'XX999999')
            ->call('lookupCompany')
            ->set('name', 'Manual Corp')
            ->set('country', 'DE')
            ->set('currency', 'EUR')
            ->call('save');

        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'Manual Corp',
        ]);
    }
}
