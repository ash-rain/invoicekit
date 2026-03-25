<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────
    // Access control
    // ──────────────────────────────────────────────────────────────────

    public function test_guests_cannot_access_invoices_index(): void
    {
        $this->get(route('invoices.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_invoices_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('invoices.index'))
            ->assertOk();
    }

    public function test_authenticated_users_can_access_create_invoice_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('invoices.create'))
            ->assertOk();
    }

    // ──────────────────────────────────────────────────────────────────
    // Invoice creation
    // ──────────────────────────────────────────────────────────────────

    public function test_user_can_create_an_invoice(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'DE']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('clientId', $client->id)
            ->set('issueDate', now()->format('Y-m-d'))
            ->set('dueDate', now()->addDays(30)->format('Y-m-d'))
            ->set('currency', 'EUR')
            ->set('items', [
                ['description' => 'Web Development', 'quantity' => '10', 'unit_price' => '100'],
            ])
            ->call('save');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'currency' => 'EUR',
        ]);
    }

    public function test_invoice_number_is_auto_generated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->assertSet('invoiceNumber', 'INV-' . now()->year . '-0001');
    }

    public function test_invoice_requires_a_client(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('clientId', null)
            ->call('save')
            ->assertHasErrors(['clientId' => 'required']);
    }

    public function test_invoice_requires_at_least_one_item(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('items', [])
            ->call('save')
            ->assertHasErrors(['items']);
    }

    public function test_due_date_must_be_after_or_equal_to_issue_date(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('clientId', $client->id)
            ->set('issueDate', '2026-03-10')
            ->set('dueDate', '2026-03-01')
            ->call('save')
            ->assertHasErrors(['dueDate']);
    }

    // ──────────────────────────────────────────────────────────────────
    // Invoice number generation
    // ──────────────────────────────────────────────────────────────────

    public function test_invoice_numbers_increment_sequentially(): void
    {
        $user = User::factory()->create();
        $year = now()->year;

        $this->assertEquals("INV-{$year}-0001", Invoice::generateNumber($user->id));

        Invoice::factory()->create([
            'user_id' => $user->id,
            'invoice_number' => "INV-{$year}-0001",
        ]);

        $this->assertEquals("INV-{$year}-0002", Invoice::generateNumber($user->id));
    }

    public function test_invoice_numbers_are_per_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $year = now()->year;

        Invoice::factory()->create(['user_id' => $user1->id, 'invoice_number' => "INV-{$year}-0001"]);

        // User2 starts from 0001 regardless of user1's invoices
        $this->assertEquals("INV-{$year}-0001", Invoice::generateNumber($user2->id));
    }

    // ──────────────────────────────────────────────────────────────────
    // VAT calculation on invoice creation
    // ──────────────────────────────────────────────────────────────────

    public function test_vat_is_calculated_correctly_for_same_country_transaction(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'country' => 'BG',
            'vat_number' => null,
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('sellerCountry', 'BG')
            ->set('clientId', $client->id)
            ->set('items', [
                ['description' => 'Service', 'quantity' => '1', 'unit_price' => '100'],
            ]);

        // BG seller to BG buyer → standard 20%
        $component->assertSet('vatType', 'standard')
            ->assertSet('vatRate', 20.0)
            ->assertSet('vatAmount', 20.0)
            ->assertSet('total', 120.0);
    }

    public function test_reverse_charge_applied_for_eu_business_buyer(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'country' => 'FR',
            'vat_number' => 'FR12345678901',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('sellerCountry', 'BG')
            ->set('clientId', $client->id)
            ->set('items', [
                ['description' => 'Service', 'quantity' => '1', 'unit_price' => '100'],
            ])
            ->assertSet('vatType', 'reverse_charge')
            ->assertSet('vatAmount', 0.0);
    }

    public function test_exempt_vat_for_non_eu_buyer(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'country' => 'US',
            'vat_number' => null,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('sellerCountry', 'BG')
            ->set('clientId', $client->id)
            ->set('items', [
                ['description' => 'Service', 'quantity' => '1', 'unit_price' => '100'],
            ])
            ->assertSet('vatType', 'exempt')
            ->assertSet('vatAmount', 0.0);
    }

    // ──────────────────────────────────────────────────────────────────
    // Invoice status transitions
    // ──────────────────────────────────────────────────────────────────

    public function test_invoice_can_be_marked_as_sent(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->call('markSent', $invoice->id);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'sent']);
    }

    public function test_invoice_can_be_marked_as_paid(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->call('markPaid', $invoice->id);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);

        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_overdue_invoice_can_be_marked_as_paid(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->overdue()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->call('markPaid', $invoice->id);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
    }

    public function test_draft_invoice_cannot_be_marked_paid_directly(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->draft()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->call('markPaid', $invoice->id);

        // Should remain draft
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'draft']);
    }

    // ──────────────────────────────────────────────────────────────────
    // Invoice visibility (data isolation)
    // ──────────────────────────────────────────────────────────────────

    public function test_invoice_list_shows_only_own_invoices(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $client1 = Client::factory()->create(['user_id' => $user1->id]);
        $client2 = Client::factory()->create(['user_id' => $user2->id]);
        $year = now()->year;

        Invoice::factory()->create([
            'user_id' => $user1->id,
            'client_id' => $client1->id,
            'invoice_number' => "INV-{$year}-0001",
        ]);
        Invoice::factory()->create([
            'user_id' => $user2->id,
            'client_id' => $client2->id,
            'invoice_number' => "INV-{$year}-0002",
        ]);

        Livewire::actingAs($user1)
            ->test(\App\Livewire\Invoices\InvoiceList::class)
            ->assertSee("INV-{$year}-0001")
            ->assertDontSee("INV-{$year}-0002");
    }

    public function test_user_cannot_view_another_users_invoice(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user2->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user2->id,
            'client_id' => $client->id,
        ]);

        $this->actingAs($user1)
            ->get(route('invoices.show', $invoice))
            ->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────
    // Invoice isOverdue model method
    // ──────────────────────────────────────────────────────────────────

    public function test_invoice_is_overdue_when_due_date_passed_and_not_paid(): void
    {
        $client = Client::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $client->user_id,
            'client_id' => $client->id,
            'status' => 'sent',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $this->assertTrue($invoice->isOverdue());
    }

    public function test_paid_invoice_is_not_overdue_even_if_due_date_passed(): void
    {
        $client = Client::factory()->create();
        $invoice = Invoice::factory()->paid()->create([
            'user_id' => $client->user_id,
            'client_id' => $client->id,
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $this->assertFalse($invoice->isOverdue());
    }

    // ──────────────────────────────────────────────────────────────────
    // Plan limit: invoice creation
    // ──────────────────────────────────────────────────────────────────

    public function test_free_plan_user_is_blocked_after_5_invoices_this_month(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $client = Client::factory()->create(['user_id' => $user->id, 'country' => 'DE']);

        // Create 5 invoices this month
        Invoice::factory()->count(5)->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'created_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\CreateInvoice::class)
            ->set('clientId', $client->id)
            ->set('issueDate', now()->format('Y-m-d'))
            ->set('dueDate', now()->addDays(30)->format('Y-m-d'))
            ->set('currency', 'EUR')
            ->set('items', [
                ['description' => 'Service', 'quantity' => '1', 'unit_price' => '100'],
            ])
            ->call('save')
            ->assertRedirect(route('billing.index'));

        $this->assertEquals(5, Invoice::where('user_id', $user->id)->count());
    }
}
