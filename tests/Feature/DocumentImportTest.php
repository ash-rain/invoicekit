<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocumentImport;
use App\Models\DocumentImport;
use App\Models\User;
use App\Notifications\DocumentImportFailedNotification;
use App\Notifications\DocumentImportSuccessNotification;
use App\Services\GeminiExtractionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_page_requires_auth_for_invoices(): void
    {
        $this->get(route('invoices.import'))
            ->assertRedirect(route('login'));
    }

    public function test_import_page_requires_auth_for_expenses(): void
    {
        $this->get(route('expenses.import'))
            ->assertRedirect(route('login'));
    }

    public function test_invoice_import_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('invoices.import'))
            ->assertOk();
    }

    public function test_expense_import_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('expenses.import'))
            ->assertOk();
    }

    public function test_process_document_import_job_updates_status_to_extracted_on_success(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->pending()->forInvoice()->create(['user_id' => $user->id]);

        $extractedData = [
            'vendor_name' => 'Test Corp',
            'invoice_number' => 'INV-2024-001',
            'issue_date' => '2024-01-15',
            'due_date' => '2024-02-15',
            'currency' => 'EUR',
            'total' => 500.00,
            'line_items' => [],
        ];

        $gemini = $this->createMock(GeminiExtractionService::class);
        $gemini->expects($this->once())
            ->method('extractFromDocument')
            ->willReturn($extractedData);

        $this->app->instance(GeminiExtractionService::class, $gemini);

        Notification::fake();

        $job = new ProcessDocumentImport($import);
        $job->handle($gemini);

        $import->refresh();

        $this->assertEquals('extracted', $import->status);
        $this->assertEquals('INV-2024-001', $import->extracted_data['invoice_number']);

        Notification::assertSentTo($user, DocumentImportSuccessNotification::class);
    }

    public function test_process_document_import_job_updates_status_to_failed_on_error(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->pending()->forInvoice()->create(['user_id' => $user->id]);

        $gemini = $this->createMock(GeminiExtractionService::class);
        $gemini->expects($this->once())
            ->method('extractFromDocument')
            ->willThrowException(new \RuntimeException('Failed to call Gemini API'));

        Notification::fake();

        $job = new ProcessDocumentImport($import);
        $job->handle($gemini);

        $import->refresh();

        $this->assertEquals('failed', $import->status);
        $this->assertStringContainsString('Failed to call Gemini API', $import->error_message);

        Notification::assertSentTo($user, DocumentImportFailedNotification::class);
    }

    public function test_process_document_import_job_sets_processing_status_before_extraction(): void
    {
        Storage::fake('minio');

        $user = User::factory()->create();
        $import = DocumentImport::factory()->pending()->create(['user_id' => $user->id]);

        $capturedStatus = null;

        $gemini = $this->createMock(GeminiExtractionService::class);
        $gemini->expects($this->once())
            ->method('extractFromDocument')
            ->willReturnCallback(function () use ($import, &$capturedStatus) {
                $import->refresh();
                $capturedStatus = $import->status;

                return ['vendor_name' => 'Test'];
            });

        Notification::fake();

        $job = new ProcessDocumentImport($import);
        $job->handle($gemini);

        $this->assertEquals('processing', $capturedStatus);
    }

    public function test_process_document_import_failed_callback_notifies_user(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->processing()->create(['user_id' => $user->id]);

        Notification::fake();

        $job = new ProcessDocumentImport($import);
        $job->failed(new \RuntimeException('Queue failure'));

        $import->refresh();

        $this->assertEquals('failed', $import->status);
        Notification::assertSentTo($user, DocumentImportFailedNotification::class);
    }

    public function test_process_document_import_job_is_queued_on_imports_queue(): void
    {
        Queue::fake();

        $import = DocumentImport::factory()->create();

        ProcessDocumentImport::dispatch($import);

        Queue::assertPushedOn('imports', ProcessDocumentImport::class);
    }

    public function test_document_import_model_status_helpers(): void
    {
        $import = DocumentImport::factory()->pending()->make();
        $this->assertTrue($import->isPending());
        $this->assertFalse($import->isProcessing());

        $import = DocumentImport::factory()->processing()->make();
        $this->assertTrue($import->isProcessing());

        $import = DocumentImport::factory()->extracted()->make();
        $this->assertTrue($import->isExtracted());

        $import = DocumentImport::factory()->completed()->make();
        $this->assertTrue($import->isCompleted());

        $import = DocumentImport::factory()->failed()->make();
        $this->assertTrue($import->isFailed());
    }

    // ─────────────────────────────────────────────────────────────────
    // Queue persistence & deleteImport
    // ─────────────────────────────────────────────────────────────────

    public function test_import_queue_shows_all_non_completed_imports_across_batches(): void
    {
        $user = User::factory()->create();

        // Imports from different batches
        $pending = DocumentImport::factory()->pending()->forInvoice()->create(['user_id' => $user->id, 'batch_id' => 'batch-1']);
        $extracted = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $user->id, 'batch_id' => 'batch-2']);
        $failed = DocumentImport::factory()->failed()->forInvoice()->create(['user_id' => $user->id, 'batch_id' => 'batch-1']);
        $completed = DocumentImport::factory()->completed()->forInvoice()->create(['user_id' => $user->id, 'batch_id' => 'batch-2']);

        // Simulate a fresh mount (new batchId) — completed import should not appear, others should
        $results = DocumentImport::where('user_id', $user->id)
            ->where('document_type', 'invoice')
            ->whereNotIn('status', ['completed'])
            ->get();

        $this->assertContains($pending->id, $results->pluck('id')->all());
        $this->assertContains($extracted->id, $results->pluck('id')->all());
        $this->assertContains($failed->id, $results->pluck('id')->all());
        $this->assertNotContains($completed->id, $results->pluck('id')->all());
    }

    public function test_delete_import_removes_record_and_file(): void
    {
        Storage::fake('minio');
        Storage::disk('minio')->put('imports/1/test.pdf', 'fake-content');

        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id,
            'stored_path' => 'imports/1/test.pdf',
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\DocumentImporter::class)
            ->call('deleteImport', $import->id);

        $this->assertDatabaseMissing('document_imports', ['id' => $import->id]);
        Storage::disk('minio')->assertMissing('imports/1/test.pdf');
    }

    public function test_delete_import_is_scoped_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $other->id]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\DocumentImporter::class)
            ->call('deleteImport', $import->id);
    }
}
