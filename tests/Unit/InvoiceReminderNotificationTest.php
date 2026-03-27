<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use Tests\TestCase;

class InvoiceReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_includes_mail_webpush_and_database_channels(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $notification = new InvoiceReminderNotification($invoice, 'due_soon');

        $this->assertContains('mail', $notification->via($user));
        $this->assertContains(WebPushChannel::class, $notification->via($user));
        $this->assertContains('database', $notification->via($user));
    }

    public function test_to_web_push_returns_message_for_due_soon(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'name' => 'Acme Corp']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'invoice_number' => 'INV-001']);

        $notification = new InvoiceReminderNotification($invoice, 'due_soon');
        $message = $notification->toWebPush($user, $notification);

        $this->assertInstanceOf(WebPushMessage::class, $message);
        $this->assertStringContainsString('Reminder', $message->toArray()['title']);
        $this->assertStringContainsString('INV-001', $message->toArray()['body']);
    }

    public function test_to_web_push_returns_overdue_message(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'name' => 'Acme Corp']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'invoice_number' => 'INV-002']);

        $notification = new InvoiceReminderNotification($invoice, 'overdue');
        $message = $notification->toWebPush($user, $notification);

        $this->assertStringContainsString('Overdue', $message->toArray()['title']);
        $this->assertStringContainsString('INV-002', $message->toArray()['body']);
    }

    public function test_to_database_returns_correct_structure(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'name' => 'Acme Corp']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'invoice_number' => 'INV-003']);

        $notification = new InvoiceReminderNotification($invoice, 'due_today');
        $data = $notification->toDatabase($user);

        $this->assertEquals('invoice_reminder', $data['type']);
        $this->assertEquals('due_today', $data['reminder_type']);
        $this->assertEquals($invoice->id, $data['invoice_id']);
        $this->assertEquals('INV-003', $data['invoice_number']);
        $this->assertEquals('Acme Corp', $data['client_name']);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('url', $data);
    }
}
