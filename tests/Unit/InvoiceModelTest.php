<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_generate_number_returns_correct_format(): void
    {
        $user = User::factory()->create();
        $year = now()->year;

        $this->assertEquals("INV-{$year}-0001", Invoice::generateNumber($user->id));
    }

    public function test_invoice_number_increments_with_existing_invoices(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $year = now()->year;

        Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => "INV-{$year}-0005",
        ]);

        $this->assertEquals("INV-{$year}-0006", Invoice::generateNumber($user->id));
    }

    public function test_invoice_is_overdue_when_sent_and_past_due_date(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'sent',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $this->assertTrue($invoice->isOverdue());
    }

    public function test_invoice_is_not_overdue_when_paid(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $invoice = Invoice::factory()->paid()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $this->assertFalse($invoice->isOverdue());
    }

    public function test_invoice_is_not_overdue_when_due_date_is_future(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'due_date' => now()->addDays(30)->toDateString(),
        ]);

        $this->assertFalse($invoice->isOverdue());
    }

    public function test_unpaid_scope_returns_sent_and_overdue(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->draft()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        Invoice::factory()->sent()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        Invoice::factory()->overdue()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        Invoice::factory()->paid()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $unpaid = Invoice::unpaid()->get();

        $this->assertCount(2, $unpaid);
        $this->assertContains('sent', $unpaid->pluck('status')->toArray());
        $this->assertContains('overdue', $unpaid->pluck('status')->toArray());
    }

    public function test_overdue_scope_returns_only_overdue(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        Invoice::factory()->sent()->create(['user_id' => $user->id, 'client_id' => $client->id]);
        Invoice::factory()->overdue()->count(2)->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $overdue = Invoice::overdue()->get();

        $this->assertCount(2, $overdue);
        $this->assertEquals(['overdue', 'overdue'], $overdue->pluck('status')->toArray());
    }

    public function test_generate_number_uses_company_invoice_prefix(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'invoice_prefix' => 'ACME']);
        $year = now()->year;

        $this->assertEquals("ACME-{$year}-0001", Invoice::generateNumber($user->id, $company));
    }

    public function test_generate_number_uses_company_starting_number(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id, 'invoice_starting_number' => 100]);
        $year = now()->year;

        $this->assertEquals("INV-{$year}-0100", Invoice::generateNumber($user->id, $company));
    }

    public function test_generate_number_starting_number_only_applies_when_no_invoices_exist(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $company = Company::factory()->create(['user_id' => $user->id, 'invoice_starting_number' => 50]);
        $year = now()->year;

        Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => "INV-{$year}-0051",
        ]);

        $this->assertEquals("INV-{$year}-0052", Invoice::generateNumber($user->id, $company));
    }

    public function test_generate_number_falls_back_to_inv_prefix_when_company_is_null(): void
    {
        $user = User::factory()->create();
        $year = now()->year;

        $this->assertEquals("INV-{$year}-0001", Invoice::generateNumber($user->id, null));
    }
}
