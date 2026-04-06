<?php

namespace Database\Factories;

use App\Models\DocumentImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DocumentImport>
 */
class DocumentImportFactory extends Factory
{
    protected $model = DocumentImport::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'batch_id' => Str::uuid(),
            'original_filename' => fake()->word().'.pdf',
            'stored_path' => 'imports/1/'.Str::uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'document_type' => fake()->randomElement(['invoice', 'expense']),
            'status' => 'pending',
            'extracted_data' => null,
            'error_message' => null,
            'invoice_id' => null,
            'expense_id' => null,
        ];
    }

    public function forInvoice(): static
    {
        return $this->state(fn () => ['document_type' => 'invoice']);
    }

    public function forExpense(): static
    {
        return $this->state(fn () => ['document_type' => 'expense']);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function processing(): static
    {
        return $this->state(fn () => ['status' => 'processing']);
    }

    public function extracted(array $data = []): static
    {
        return $this->state(fn () => [
            'status' => 'extracted',
            'extracted_data' => $data ?: $this->sampleInvoiceData(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function failed(string $message = 'Extraction failed'): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'error_message' => $message,
        ]);
    }

    /** @return array<string, mixed> */
    private function sampleInvoiceData(): array
    {
        return [
            'vendor_name' => fake()->company(),
            'vendor_address' => fake()->address(),
            'vendor_vat_number' => 'DE'.fake()->numerify('###########'),
            'client_name' => fake()->company(),
            'client_address' => fake()->address(),
            'client_vat_number' => null,
            'invoice_number' => 'INV-'.fake()->year().'-'.fake()->numerify('####'),
            'issue_date' => now()->subDays(5)->format('Y-m-d'),
            'due_date' => now()->addDays(25)->format('Y-m-d'),
            'currency' => 'EUR',
            'line_items' => [
                [
                    'description' => 'Professional Services',
                    'quantity' => 1,
                    'unit_price' => 500.00,
                    'vat_rate' => 19,
                ],
            ],
            'subtotal' => 500.00,
            'vat_amount' => 95.00,
            'total' => 595.00,
            'notes' => null,
        ];
    }
}
