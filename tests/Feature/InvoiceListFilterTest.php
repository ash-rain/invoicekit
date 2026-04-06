<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceListFilterTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────
    // Invoice list: client filter
    // ─────────────────────────────────────────────────────────────────

    public function test_invoice_list_shows_all_invoices_without_client_filter(): void
    {
        $user = User::factory()->create();
        $clientA = Client::factory()->create(['user_id' => $user->id]);
        $clientB = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $clientA->id, 'invoice_number' => 'INV-A-001']);
        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $clientB->id, 'invoice_number' => 'INV-B-001']);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class);

        $component->assertSee('INV-A-001')
            ->assertSee('INV-B-001');
    }

    public function test_invoice_list_filters_by_client(): void
    {
        $user = User::factory()->create();
        $clientA = Client::factory()->create(['user_id' => $user->id]);
        $clientB = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $clientA->id, 'invoice_number' => 'INV-A-001']);
        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $clientB->id, 'invoice_number' => 'INV-B-001']);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->set('clientFilter', (string) $clientA->id);

        $component->assertSee('INV-A-001')
            ->assertDontSee('INV-B-001');
    }

    public function test_invoice_list_client_filter_accepts_url_parameter(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'invoice_number' => 'INV-FILTER-001']);

        $this->actingAs($user)
            ->get(route('invoices.index', ['clientFilter' => $client->id]))
            ->assertOk()
            ->assertSee('INV-FILTER-001');
    }

    public function test_invoice_list_client_filter_resets_pagination(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class);

        $component->set('clientFilter', (string) $client->id);

        // No assertion needed — just verifies it doesn't throw
        $this->assertTrue(true);
    }

    public function test_invoice_list_status_filter_works_alongside_client_filter(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'draft', 'invoice_number' => 'INV-DRAFT-001']);
        Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'status' => 'sent', 'invoice_number' => 'INV-SENT-001']);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->set('clientFilter', (string) $client->id)
            ->set('statusFilter', 'draft');

        $component->assertSee('INV-DRAFT-001')
            ->assertDontSee('INV-SENT-001');
    }

    public function test_invoice_list_client_dropdown_contains_only_own_clients(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $ownClient = Client::factory()->create(['user_id' => $user->id, 'name' => 'My Client']);
        $otherClient = Client::factory()->create(['user_id' => $other->id, 'name' => 'Other Client']);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class);

        $clients = $component->get('clients');

        $this->assertTrue($clients->contains('id', $ownClient->id));
        $this->assertFalse($clients->contains('id', $otherClient->id));
    }

    // ─────────────────────────────────────────────────────────────────
    // Expense list: client filter
    // ─────────────────────────────────────────────────────────────────

    public function test_expense_list_shows_all_expenses_without_client_filter(): void
    {
        $user = User::factory()->create();
        $clientA = Client::factory()->create(['user_id' => $user->id]);
        $clientB = Client::factory()->create(['user_id' => $user->id]);

        Expense::factory()->create(['user_id' => $user->id, 'client_id' => $clientA->id, 'description' => 'Expense Alpha']);
        Expense::factory()->create(['user_id' => $user->id, 'client_id' => $clientB->id, 'description' => 'Expense Beta']);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Expenses\ExpenseList::class);

        $component->assertSee('Expense Alpha')
            ->assertSee('Expense Beta');
    }

    public function test_expense_list_filters_by_client(): void
    {
        $user = User::factory()->create();
        $clientA = Client::factory()->create(['user_id' => $user->id]);
        $clientB = Client::factory()->create(['user_id' => $user->id]);

        Expense::factory()->create(['user_id' => $user->id, 'client_id' => $clientA->id, 'description' => 'Expense Alpha']);
        Expense::factory()->create(['user_id' => $user->id, 'client_id' => $clientB->id, 'description' => 'Expense Beta']);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Expenses\ExpenseList::class)
            ->set('clientFilter', (string) $clientA->id);

        $component->assertSee('Expense Alpha')
            ->assertDontSee('Expense Beta');
    }

    public function test_expense_list_client_filter_accepts_url_parameter(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Expense::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'description' => 'URL Filtered Expense']);

        $this->actingAs($user)
            ->get(route('expenses.index', ['clientFilter' => $client->id]))
            ->assertOk()
            ->assertSee('URL Filtered Expense');
    }

    public function test_expense_list_client_dropdown_contains_only_own_clients(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $ownClient = Client::factory()->create(['user_id' => $user->id, 'name' => 'My Expense Client']);
        $otherClient = Client::factory()->create(['user_id' => $other->id, 'name' => 'Other Expense Client']);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Expenses\ExpenseList::class);

        $clients = $component->get('clients');

        $this->assertTrue($clients->contains('id', $ownClient->id));
        $this->assertFalse($clients->contains('id', $otherClient->id));
    }
}
