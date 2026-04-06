<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_detail_requires_auth(): void
    {
        $client = Client::factory()->create();

        $this->get(route('clients.show', $client))
            ->assertRedirect(route('login'));
    }

    public function test_client_detail_forbids_access_to_another_users_client(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->get(route('clients.show', $client))
            ->assertForbidden();
    }

    public function test_client_detail_page_renders_for_own_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('clients.show', $client))
            ->assertOk();
    }

    public function test_client_detail_shows_client_name(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'name' => 'Awesome Client Ltd']);

        $this->actingAs($user)
            ->get(route('clients.show', $client))
            ->assertOk()
            ->assertSee('Awesome Client Ltd');
    }

    public function test_total_invoiced_sums_sent_paid_and_overdue_invoices(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'sent', 'total' => 100.00]);
        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'paid', 'total' => 200.00]);
        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'overdue', 'total' => 50.00]);
        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'draft', 'total' => 999.00]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\ClientDetail::class, ['client' => $client]);

        $this->assertEquals(350.00, $component->get('totalInvoiced'));
    }

    public function test_total_paid_sums_only_paid_invoices(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'paid', 'total' => 300.00]);
        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'sent', 'total' => 150.00]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\ClientDetail::class, ['client' => $client]);

        $this->assertEquals(300.00, $component->get('totalPaid'));
    }

    public function test_total_outstanding_sums_sent_and_overdue_invoices(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'sent', 'total' => 100.00]);
        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'overdue', 'total' => 75.00]);
        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'paid', 'total' => 500.00]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\ClientDetail::class, ['client' => $client]);

        $this->assertEquals(175.00, $component->get('totalOutstanding'));
    }

    public function test_total_expenses_sums_all_expenses_for_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $other = Client::factory()->create(['user_id' => $user->id]);

        Expense::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'amount' => 50.00]);
        Expense::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'amount' => 80.00]);
        Expense::factory()->create(['user_id' => $user->id, 'client_id' => $other->id, 'amount' => 999.00]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\ClientDetail::class, ['client' => $client]);

        $this->assertEquals(130.00, $component->get('totalExpenses'));
    }

    public function test_recent_invoices_limited_to_ten(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->count(15)->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\ClientDetail::class, ['client' => $client]);

        $this->assertCount(10, $component->get('recentInvoices'));
    }

    public function test_recent_expenses_limited_to_ten(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Expense::factory()->count(15)->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\ClientDetail::class, ['client' => $client]);

        $this->assertCount(10, $component->get('recentExpenses'));
    }

    public function test_recent_invoices_only_show_own_client_invoices(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $otherClient = Client::factory()->create(['user_id' => $other->id]);

        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        Invoice::factory()->create(['user_id' => $other->id, 'client_id' => $otherClient->id]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\ClientDetail::class, ['client' => $client]);

        $this->assertCount(1, $component->get('recentInvoices'));
    }

    public function test_stats_return_zero_when_no_invoices_or_expenses(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Clients\ClientDetail::class, ['client' => $client]);

        $this->assertEquals(0.0, $component->get('totalInvoiced'));
        $this->assertEquals(0.0, $component->get('totalPaid'));
        $this->assertEquals(0.0, $component->get('totalOutstanding'));
        $this->assertEquals(0.0, $component->get('totalExpenses'));
    }
}
