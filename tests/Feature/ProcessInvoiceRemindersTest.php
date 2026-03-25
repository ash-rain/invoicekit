<?php

namespace Tests\Feature;

use App\Jobs\SendInvoiceReminder;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessInvoiceRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_sent_invoices_past_due_date_are_flagged_as_overdue(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $invoice = Invoice::factory()->sent()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->artisan('invoices:process-reminders')
            ->assertSuccessful();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'overdue',
        ]);
    }

    public function test_paid_invoices_are_not_flagged_as_overdue(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $invoice = Invoice::factory()->paid()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->artisan('invoices:process-reminders')
            ->assertSuccessful();

        // Paid invoice should remain paid
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
    }

    public function test_reminder_jobs_dispatched_for_due_soon_invoices(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'email' => 'client@example.com']);

        Invoice::factory()->sent()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->artisan('invoices:process-reminders');

        Queue::assertPushed(SendInvoiceReminder::class, function ($job) {
            return $job->reminderType === 'due_soon';
        });
    }

    public function test_reminder_jobs_dispatched_for_due_today_invoices(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'email' => 'client@example.com']);

        Invoice::factory()->sent()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'due_date' => now()->toDateString(),
        ]);

        $this->artisan('invoices:process-reminders');

        Queue::assertPushed(SendInvoiceReminder::class, function ($job) {
            return $job->reminderType === 'due_today';
        });
    }

    public function test_reminder_jobs_dispatched_for_seven_days_overdue_invoices(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'email' => 'client@example.com']);

        Invoice::factory()->overdue()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'due_date' => now()->subDays(7)->toDateString(),
        ]);

        $this->artisan('invoices:process-reminders');

        Queue::assertPushed(SendInvoiceReminder::class, function ($job) {
            return $job->reminderType === 'overdue';
        });
    }
}
