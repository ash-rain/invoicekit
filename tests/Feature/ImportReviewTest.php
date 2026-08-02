<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DocumentImport;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportReviewTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────
    // Access control
    // ─────────────────────────────────────────────────────────────────

    public function test_invoice_review_requires_auth(): void
    {
        $import = DocumentImport::factory()->extracted()->forInvoice()->create();

        $this->get(route('invoices.import.review', $import))
            ->assertRedirect(route('login'));
    }

    public function test_expense_review_requires_auth(): void
    {
        $import = DocumentImport::factory()->extracted()->forExpense()->create();

        $this->get(route('expenses.import.review', $import))
            ->assertRedirect(route('login'));
    }

    public function test_invoice_review_forbids_access_to_another_users_import(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->get(route('invoices.import.review', $import))
            ->assertForbidden();
    }

    public function test_expense_review_forbids_access_to_another_users_import(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forExpense()->create(['user_id' => $other->id]);

        $this->actingAs($user)
            ->get(route('expenses.import.review', $import))
            ->assertForbidden();
    }

    public function test_invoice_review_returns_404_for_non_extracted_import(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->pending()->forInvoice()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('invoices.import.review', $import))
            ->assertNotFound();
    }

    public function test_expense_review_returns_404_for_non_extracted_import(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->pending()->forExpense()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('expenses.import.review', $import))
            ->assertNotFound();
    }

    // ─────────────────────────────────────────────────────────────────
    // Invoice review renders
    // ─────────────────────────────────────────────────────────────────

    public function test_invoice_review_page_renders(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('invoices.import.review', $import))
            ->assertOk();
    }

    public function test_expense_review_page_renders(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forExpense()->create([
            'user_id' => $user->id,
            'extracted_data' => [
                'description' => 'Office supplies',
                'amount' => 150.00,
                'currency' => 'EUR',
                'category' => 'other',
                'date' => now()->format('Y-m-d'),
            ],
        ]);

        $this->actingAs($user)
            ->get(route('expenses.import.review', $import))
            ->assertOk();
    }

    // ─────────────────────────────────────────────────────────────────
    // Invoice confirm creates records
    // ─────────────────────────────────────────────────────────────────

    public function test_invoice_review_confirm_creates_invoice_and_items(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $import = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id,
            'extracted_data' => [
                'invoice_number' => 'INV-2024-0001',
                'issue_date' => '2024-01-15',
                'due_date' => '2024-02-15',
                'currency' => 'EUR',
                'vat_rate' => 20,
                'notes' => 'Test note',
                'line_items' => [
                    ['description' => 'Service A', 'quantity' => 2, 'unit_price' => 100.00],
                ],
            ],
        ]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\ImportReview::class, ['import' => $import]);

        $component->set('clientId', $client->id)
            ->set('invoiceNumber', 'INV-2024-0001')
            ->set('issueDate', '2024-01-15')
            ->set('dueDate', '2024-02-15')
            ->set('currency', 'EUR')
            ->set('vatRate', 20)
            ->set('items', [['description' => 'Service A', 'quantity' => '2', 'unit_price' => '100.00']])
            ->call('confirm');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-2024-0001',
            'status' => 'draft',
        ]);

        $invoice = Invoice::where('invoice_number', 'INV-2024-0001')->first();
        $this->assertNotNull($invoice);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Service A',
        ]);

        $import->refresh();
        $this->assertEquals('completed', $import->status);
        $this->assertEquals($invoice->id, $import->invoice_id);
    }

    public function test_invoice_review_skip_marks_import_completed_without_creating_invoice(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $user->id]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\ImportReview::class, ['import' => $import])
            ->call('skip');

        $import->refresh();
        $this->assertEquals('completed', $import->status);
        $this->assertNull($import->invoice_id);

        $this->assertDatabaseCount('invoices', 0);
    }

    // ─────────────────────────────────────────────────────────────────
    // Expense confirm creates records with receipt copy
    // ─────────────────────────────────────────────────────────────────

    public function test_expense_review_confirm_creates_expense(): void
    {
        Storage::fake('minio');
        Storage::disk('minio')->put('imports/1/invoice.pdf', 'fake-pdf-content');

        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forExpense()->create([
            'user_id' => $user->id,
            'stored_path' => 'imports/1/invoice.pdf',
            'extracted_data' => [
                'description' => 'Office supplies',
                'amount' => 150.00,
                'currency' => 'EUR',
                'category' => 'other',
                'date' => now()->format('Y-m-d'),
            ],
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Expenses\ImportReview::class, ['import' => $import])
            ->set('description', 'Office supplies')
            ->set('amount', '150.00')
            ->set('currency', 'EUR')
            ->set('category', 'other')
            ->set('date', now()->format('Y-m-d'))
            ->call('confirm');

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'description' => 'Office supplies',
            'amount' => 150.00,
        ]);

        $expense = Expense::where('user_id', $user->id)->first();
        $this->assertNotNull($expense->receipt_file);

        $import->refresh();
        $this->assertEquals('completed', $import->status);
        $this->assertEquals($expense->id, $import->expense_id);
    }

    public function test_expense_review_confirm_copies_receipt_file_to_receipts_disk(): void
    {
        Storage::fake('minio');
        Storage::disk('minio')->put('imports/1/receipt.pdf', 'fake-receipt-content');

        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forExpense()->create([
            'user_id' => $user->id,
            'stored_path' => 'imports/1/receipt.pdf',
            'extracted_data' => [
                'description' => 'Travel expense',
                'amount' => 75.00,
                'currency' => 'EUR',
                'category' => 'travel',
                'date' => now()->format('Y-m-d'),
            ],
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Expenses\ImportReview::class, ['import' => $import])
            ->set('description', 'Travel expense')
            ->set('amount', '75.00')
            ->set('currency', 'EUR')
            ->set('category', 'travel')
            ->set('date', now()->format('Y-m-d'))
            ->call('confirm');

        $expense = Expense::where('user_id', $user->id)->first();
        $this->assertNotNull($expense->receipt_file);
        $this->assertStringStartsWith('receipts/', $expense->receipt_file);

        // File should exist in receipts directory
        Storage::disk('minio')->assertExists($expense->receipt_file);
    }

    public function test_expense_review_skip_marks_import_completed_without_creating_expense(): void
    {
        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forExpense()->create([
            'user_id' => $user->id,
            'extracted_data' => [
                'description' => 'skip me',
                'amount' => 10.00,
                'currency' => 'EUR',
                'category' => 'other',
                'date' => now()->format('Y-m-d'),
            ],
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Expenses\ImportReview::class, ['import' => $import])
            ->call('skip');

        $import->refresh();
        $this->assertEquals('completed', $import->status);
        $this->assertNull($import->expense_id);
        $this->assertDatabaseCount('expenses', 0);
    }

    // ─────────────────────────────────────────────────────────────────
    // Delete import
    // ─────────────────────────────────────────────────────────────────

    public function test_invoice_review_delete_removes_import_and_redirects(): void
    {
        Storage::fake('minio');
        Storage::disk('minio')->put('imports/1/invoice.pdf', 'fake-content');

        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id,
            'stored_path' => 'imports/1/invoice.pdf',
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\ImportReview::class, ['import' => $import])
            ->call('deleteImport')
            ->assertRedirect(route('invoices.import'));

        $this->assertDatabaseMissing('document_imports', ['id' => $import->id]);
        Storage::disk('minio')->assertMissing('imports/1/invoice.pdf');
    }

    public function test_expense_review_delete_removes_import_and_redirects(): void
    {
        Storage::fake('minio');
        Storage::disk('minio')->put('imports/1/expense.pdf', 'fake-content');

        $user = User::factory()->create();
        $import = DocumentImport::factory()->extracted()->forExpense()->create([
            'user_id' => $user->id,
            'stored_path' => 'imports/1/expense.pdf',
            'extracted_data' => [
                'description' => 'Test',
                'amount' => 10.00,
                'currency' => 'EUR',
                'category' => 'other',
                'date' => now()->format('Y-m-d'),
            ],
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Expenses\ImportReview::class, ['import' => $import])
            ->call('deleteImport')
            ->assertRedirect(route('expenses.import'));

        $this->assertDatabaseMissing('document_imports', ['id' => $import->id]);
        Storage::disk('minio')->assertMissing('imports/1/expense.pdf');
    }

    // ─────────────────────────────────────────────────────────────────
    // Client auto-match
    // ─────────────────────────────────────────────────────────────────

    // ─────────────────────────────────────────────────────────────────
    // Queue: advance to next extracted after action
    // ─────────────────────────────────────────────────────────────────

    public function test_invoice_confirm_redirects_to_next_extracted_when_more_in_queue(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $first = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id,
            'extracted_data' => [
                'invoice_number' => 'INV-A',
                'issue_date' => '2026-01-01',
                'due_date' => '2026-02-01',
                'currency' => 'EUR',
                'line_items' => [],
            ],
        ]);
        $second = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id,
            'created_at' => $first->created_at->addSecond(),
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\ImportReview::class, ['import' => $first])
            ->set('clientId', $client->id)
            ->set('invoiceNumber', 'INV-A')
            ->set('issueDate', '2026-01-01')
            ->set('dueDate', '2026-02-01')
            ->set('currency', 'EUR')
            ->set('vatRate', 0)
            ->set('items', [['description' => 'Item', 'quantity' => '1', 'unit_price' => '10']])
            ->call('confirm')
            ->assertRedirect(route('invoices.import.review', $second));
    }

    public function test_invoice_delete_redirects_to_next_extracted_when_more_in_queue(): void
    {
        $user = User::factory()->create();

        $first = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $user->id]);
        $second = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id,
            'created_at' => $first->created_at->addSecond(),
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\ImportReview::class, ['import' => $first])
            ->call('deleteImport')
            ->assertRedirect(route('invoices.import.review', $second));
    }

    public function test_expense_confirm_redirects_to_next_extracted_when_more_in_queue(): void
    {
        Storage::fake('minio');

        $user = User::factory()->create();

        $first = DocumentImport::factory()->extracted()->forExpense()->create([
            'user_id' => $user->id,
            'extracted_data' => [
                'description' => 'first',
                'amount' => 5.00,
                'currency' => 'EUR',
                'category' => 'other',
                'date' => '2026-01-01',
            ],
        ]);
        $second = DocumentImport::factory()->extracted()->forExpense()->create([
            'user_id' => $user->id,
            'created_at' => $first->created_at->addSecond(),
            'extracted_data' => [
                'description' => 'second',
                'amount' => 5.00,
                'currency' => 'EUR',
                'category' => 'other',
                'date' => '2026-01-02',
            ],
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Expenses\ImportReview::class, ['import' => $first])
            ->set('description', 'first')
            ->set('amount', '5.00')
            ->set('currency', 'EUR')
            ->set('category', 'other')
            ->set('date', '2026-01-01')
            ->call('confirm')
            ->assertRedirect(route('expenses.import.review', $second));
    }

    public function test_invoice_go_to_import_redirects_to_target_review(): void
    {
        $user = User::factory()->create();
        $first = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $user->id]);
        $second = DocumentImport::factory()->extracted()->forInvoice()->create(['user_id' => $user->id]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\ImportReview::class, ['import' => $first])
            ->call('goToImport', $second->id)
            ->assertRedirect(route('invoices.import.review', $second));
    }

    public function test_invoice_queue_position_and_total_reflect_batch(): void
    {
        $user = User::factory()->create();
        $batch = (string) \Illuminate\Support\Str::uuid();

        $a = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id, 'batch_id' => $batch,
        ]);
        $b = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id, 'batch_id' => $batch, 'created_at' => $a->created_at->addSecond(),
        ]);
        $c = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id, 'batch_id' => $batch, 'created_at' => $a->created_at->addSeconds(2),
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\ImportReview::class, ['import' => $b])
            ->assertSet('positionInQueue', 2)
            ->assertSet('totalInQueue', 3);
    }

    public function test_invoice_review_auto_matches_client_by_vat_number(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'vat_number' => 'DE123456789',
        ]);

        $import = DocumentImport::factory()->extracted()->forInvoice()->create([
            'user_id' => $user->id,
            'extracted_data' => [
                'vendor_vat' => 'DE123456789',
                'vendor_name' => 'Some Corp',
                'invoice_number' => 'INV-TEST',
                'issue_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'currency' => 'EUR',
                'line_items' => [],
            ],
        ]);

        $component = \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Invoices\ImportReview::class, ['import' => $import]);

        $this->assertEquals($client->id, $component->get('clientId'));
    }
}
