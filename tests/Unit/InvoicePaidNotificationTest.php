<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoicePaidNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use Tests\TestCase;

class InvoicePaidNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_includes_webpush_and_database_but_not_mail(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

        $notification = new InvoicePaidNotification($invoice);

        $this->assertContains(WebPushChannel::class, $notification->via($user));
        $this->assertContains('database', $notification->via($user));
        $this->assertNotContains('mail', $notification->via($user));
    }

    public function test_to_web_push_contains_invoice_number_and_client_name(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'name' => 'Acme Corp']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'invoice_number' => 'INV-010']);

        $notification = new InvoicePaidNotification($invoice);
        $message = $notification->toWebPush($user, $notification);

        $this->assertInstanceOf(WebPushMessage::class, $message);
        $this->assertStringContainsString('Paid', $message->toArray()['title']);
        $this->assertStringContainsString('INV-010', $message->toArray()['body']);
        $this->assertStringContainsString('Acme Corp', $message->toArray()['body']);
    }

    public function test_to_database_returns_correct_structure(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id, 'name' => 'Acme Corp']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'invoice_number' => 'INV-010']);

        $notification = new InvoicePaidNotification($invoice);
        $data = $notification->toDatabase($user);

        $this->assertEquals('invoice_paid', $data['type']);
        $this->assertEquals($invoice->id, $data['invoice_id']);
        $this->assertEquals('INV-010', $data['invoice_number']);
        $this->assertEquals('Acme Corp', $data['client_name']);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('url', $data);
    }
}
