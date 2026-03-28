<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\RecurringInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_recurring_invoice_list_requires_auth(): void
    {
        $this->get(route('recurring-invoices.index'))
            ->assertRedirect(route('login'));
    }

    public function test_recurring_invoice_list_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('recurring-invoices.index'))
            ->assertOk()
            ->assertSeeLivewire('invoices.recurring-invoice-list');
    }

    public function test_recurring_invoice_list_shows_only_own_templates(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $own = RecurringInvoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        $foreign = RecurringInvoice::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->get(route('recurring-invoices.index'))
            ->assertOk()
            ->assertSee($client->name)
            ->assertDontSee($foreign->client->name);
    }

    public function test_create_recurring_invoice_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('recurring-invoices.create'))
            ->assertOk()
            ->assertSeeLivewire('invoices.create-recurring-invoice');
    }

    public function test_edit_recurring_invoice_page_renders(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $recurring = RecurringInvoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $this->actingAs($user)
            ->get(route('recurring-invoices.edit', $recurring))
            ->assertOk()
            ->assertSeeLivewire('invoices.create-recurring-invoice');
    }

    public function test_process_recurring_command_generates_invoice_due_today(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $recurring = RecurringInvoice::factory()->dueToday()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'frequency' => 'monthly',
            'subtotal' => 1000.00,
            'vat_rate' => 19.00,
            'vat_amount' => 190.00,
            'total' => 1190.00,
        ]);

        $recurring->items()->create([
            'description' => 'Development work',
            'quantity' => 10,
            'unit_price' => 100.00,
            'subtotal' => 1000.00,
        ]);

        $this->artisan('invoices:process-recurring')
            ->assertSuccessful();

        $this->assertDatabaseHas('invoices', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'total' => 1190.00,
        ]);
    }

    public function test_process_recurring_command_skips_paused_templates(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        RecurringInvoice::factory()->paused()->dueToday()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $this->artisan('invoices:process-recurring')
            ->assertSuccessful();

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_process_recurring_command_skips_future_dates(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        RecurringInvoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'next_send_date' => now()->addDays(5)->toDateString(),
        ]);

        $this->artisan('invoices:process-recurring')
            ->assertSuccessful();

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_generate_invoice_advances_next_send_date_monthly(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $recurring = RecurringInvoice::factory()->monthly()->dueToday()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $today = Carbon::today();

        $recurring->generateInvoice();

        $recurring->refresh();

        $this->assertTrue($recurring->next_send_date->equalTo($today->copy()->addMonth()));
        $this->assertTrue($recurring->last_sent_date->isToday());
    }

    public function test_generate_invoice_advances_next_send_date_quarterly(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $recurring = RecurringInvoice::factory()->dueToday()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'frequency' => 'quarterly',
        ]);

        $today = Carbon::today();

        $recurring->generateInvoice();

        $recurring->refresh();

        $this->assertTrue($recurring->next_send_date->equalTo($today->copy()->addMonths(3)));
    }

    public function test_generate_invoice_advances_next_send_date_annually(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $recurring = RecurringInvoice::factory()->dueToday()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'frequency' => 'annually',
        ]);

        $today = Carbon::today();

        $recurring->generateInvoice();

        $recurring->refresh();

        $this->assertTrue($recurring->next_send_date->equalTo($today->copy()->addYear()));
    }

    public function test_generate_invoice_copies_line_items(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $recurring = RecurringInvoice::factory()->dueToday()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $recurring->items()->create([
            'description' => 'Monthly support',
            'quantity' => 1,
            'unit_price' => 500.00,
            'subtotal' => 500.00,
        ]);

        $invoice = $recurring->generateInvoice();

        $this->assertCount(1, $invoice->items);
        $this->assertEquals('Monthly support', $invoice->items->first()->description);
    }
}
