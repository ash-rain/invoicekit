<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
