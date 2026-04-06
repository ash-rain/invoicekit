<?php

namespace Tests\Feature;

use App\Models\DocumentImport;
use App\Models\User;
use App\Notifications\DocumentImportFailedNotification;
use App\Notifications\DocumentImportSuccessNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ImportNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_notification_uses_invoice_review_route_for_invoice_import(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $user->id]);

        $notification = new DocumentImportSuccessNotification($import);

        $dbPayload = $notification->toDatabase($user);

        $expectedUrl = route('invoices.import.review', $import);
        $this->assertEquals($expectedUrl, $dbPayload['url']);
    }

    public function test_success_notification_uses_expense_review_route_for_expense_import(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forExpense()->create(['user_id' => $user->id]);

        $notification = new DocumentImportSuccessNotification($import);

        $dbPayload = $notification->toDatabase($user);

        $expectedUrl = route('expenses.import.review', $import);
        $this->assertEquals($expectedUrl, $dbPayload['url']);
    }

    public function test_success_notification_database_payload_contains_required_fields(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id,
            'original_filename' => 'invoice_jan.pdf',
        ]);

        $notification = new DocumentImportSuccessNotification($import);
        $payload = $notification->toDatabase($user);

        $this->assertEquals('document_import_success', $payload['type']);
        $this->assertEquals($import->id, $payload['import_id']);
        $this->assertEquals('invoice_jan.pdf', $payload['filename']);
        $this->assertEquals('invoice', $payload['document_type']);
        $this->assertArrayHasKey('message', $payload);
        $this->assertArrayHasKey('url', $payload);
    }

    public function test_failed_notification_database_payload_contains_required_fields(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->failed('Gemini API timeout')->forInvoice()->create([
            'user_id' => $user->id,
            'original_filename' => 'broken_scan.pdf',
        ]);

        $notification = new DocumentImportFailedNotification($import);
        $payload = $notification->toDatabase($user);

        $this->assertEquals('document_import_failed', $payload['type']);
        $this->assertEquals($import->id, $payload['import_id']);
        $this->assertEquals('broken_scan.pdf', $payload['filename']);
        $this->assertStringContainsString('broken_scan.pdf', $payload['message']);
        $this->assertNull($payload['url']);
    }

    public function test_failed_notification_includes_error_message_in_payload(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->failed('Rate limit exceeded')->create(['user_id' => $user->id]);

        $notification = new DocumentImportFailedNotification($import);
        $payload = $notification->toDatabase($user);

        $this->assertStringContainsString('Rate limit exceeded', $payload['message']);
    }

    public function test_success_notification_via_returns_correct_channels(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $user->id]);

        $notification = new DocumentImportSuccessNotification($import);
        $channels = $notification->via($user);

        $this->assertContains('database', $channels);
    }

    public function test_failed_notification_via_returns_correct_channels(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->failed()->create(['user_id' => $user->id]);

        $notification = new DocumentImportFailedNotification($import);
        $channels = $notification->via($user);

        $this->assertContains('database', $channels);
    }

    public function test_success_notification_is_sent_when_faked(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $user->id]);

        $user->notify(new DocumentImportSuccessNotification($import));

        Notification::assertSentTo($user, DocumentImportSuccessNotification::class);
    }

    public function test_failed_notification_is_sent_when_faked(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $import = DocumentImport::factory()->failed()->create(['user_id' => $user->id]);

        $user->notify(new DocumentImportFailedNotification($import));

        Notification::assertSentTo($user, DocumentImportFailedNotification::class);
    }

    public function test_success_notification_webpush_contains_filename_in_body(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id,
            'original_filename' => 'my_invoice.pdf',
        ]);

        $notification = new DocumentImportSuccessNotification($import);
        $webPush = $notification->toWebPush($user, $notification);

        $data = $webPush->toArray();

        $this->assertStringContainsString('my_invoice.pdf', $data['body']);
    }
}
