<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\DocumentImport;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ImportReview extends Component
{
    public DocumentImport $import;

    public ?int $clientId = null;

    public string $invoiceNumber = '';

    public string $issueDate = '';

    public string $dueDate = '';

    public string $currency = 'EUR';

    public string $notes = '';

    public array $items = [];

    public float $vatRate = 0.0;

    public function mount(DocumentImport $import): void
    {
        if ($import->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $import->isExtracted()) {
            abort(404);
        }

        $this->import = $import;
        $data = $import->extracted_data ?? [];

        $this->invoiceNumber = $data['invoice_number'] ?? Invoice::generateNumber(Auth::id(), Auth::user()->currentCompany);
        $this->issueDate = $data['issue_date'] ?? now()->format('Y-m-d');
        $this->dueDate = $data['due_date'] ?? now()->addDays(30)->format('Y-m-d');
        $this->currency = $data['currency'] ?? 'EUR';
        $this->notes = (isset($data['notes']) && $data['notes'] !== null && (string) $data['notes'] !== 'null')
            ? (string) $data['notes']
            : '';
        $this->vatRate = (float) ($data['vat_rate'] ?? 0);

        // Pre-fill line items from extracted data
        $this->items = collect($data['line_items'] ?? [])->map(fn ($item) => [
            'description' => $item['description'] ?? '',
            'quantity' => (string) ($item['quantity'] ?? 1),
            'unit_price' => (string) ($item['unit_price'] ?? '0.00'),
        ])->toArray();

        if (empty($this->items)) {
            $this->items = [['description' => '', 'quantity' => '1', 'unit_price' => '0.00']];
        }

        // Auto-match client by VAT number or name
        $this->clientId = $this->findMatchingClient($data);
    }

    private function findMatchingClient(array $data): ?int
    {
        $vendorVat = $data['vendor_vat'] ?? $data['client_vat'] ?? null;
        $clientName = $data['client_name'] ?? $data['vendor_name'] ?? null;

        if ($vendorVat) {
            $client = Client::where('user_id', Auth::id())
                ->where('vat_number', $vendorVat)
                ->first();
            if ($client) {
                return $client->id;
            }
        }

        if ($clientName) {
            $client = Client::where('user_id', Auth::id())
                ->where('name', 'like', "%{$clientName}%")
                ->first();
            if ($client) {
                return $client->id;
            }
        }

        return null;
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'quantity' => '1', 'unit_price' => '0.00'];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            array_splice($this->items, $index, 1);
        }
    }

    #[Computed]
    public function fileUrl(): ?string
    {
        return $this->import->stored_path
            ? Storage::disk('minio')->url($this->import->stored_path)
            : null;
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->items)->sum(
            fn ($item) => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0),
        );
    }

    #[Computed]
    public function vatAmount(): float
    {
        return round($this->subtotal * $this->vatRate / 100, 2);
    }

    #[Computed]
    public function total(): float
    {
        return round($this->subtotal + $this->vatAmount, 2);
    }

    #[Computed]
    public function extractedClientData(): array
    {
        $data = $this->import->extracted_data ?? [];

        return array_filter([
            'name' => $data['client_name'] ?? $data['vendor_name'] ?? null,
            'vat_number' => $data['client_vat'] ?? $data['vendor_vat'] ?? null,
            'email' => $data['client_email'] ?? $data['vendor_email'] ?? null,
            'address' => $data['client_address'] ?? $data['vendor_address'] ?? null,
        ]);
    }

    #[Computed]
    public function clients()
    {
        return Client::where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function queue()
    {
        return DocumentImport::where('user_id', Auth::id())
            ->where('document_type', 'invoice')
            ->where('batch_id', $this->import->batch_id)
            ->orderBy('created_at')
            ->get();
    }

    #[Computed]
    public function positionInQueue(): int
    {
        $index = $this->queue->search(fn (DocumentImport $i) => $i->id === $this->import->id);

        return $index === false ? 1 : $index + 1;
    }

    #[Computed]
    public function totalInQueue(): int
    {
        return max(1, $this->queue->count());
    }

    #[Computed]
    public function prevReviewable(): ?DocumentImport
    {
        $current = $this->import;

        return $this->queue
            ->filter(fn (DocumentImport $i) => $i->id !== $current->id
                && $i->isExtracted()
                && $i->created_at <= $current->created_at)
            ->last();
    }

    #[Computed]
    public function nextReviewable(): ?DocumentImport
    {
        $current = $this->import;

        return $this->queue
            ->filter(fn (DocumentImport $i) => $i->id !== $current->id
                && $i->isExtracted()
                && $i->created_at >= $current->created_at)
            ->first();
    }

    public function goToImport(int $importId): void
    {
        $this->redirect(route('invoices.import.review', $importId), navigate: true);
    }

    private function redirectAfterAction(string $fallbackRoute): void
    {
        $next = DocumentImport::where('user_id', Auth::id())
            ->where('document_type', 'invoice')
            ->where('id', '!=', $this->import->id)
            ->where('status', 'extracted')
            ->orderBy('created_at')
            ->first();

        if ($next) {
            $this->redirect(route('invoices.import.review', $next), navigate: true);

            return;
        }

        $this->redirect(route($fallbackRoute), navigate: true);
    }

    public function confirm(): void
    {
        $this->validate([
            'clientId' => ['required', 'integer', Rule::exists('clients', 'id')->where('user_id', Auth::id())],
            'invoiceNumber' => ['required', 'string', 'max:100'],
            'issueDate' => ['required', 'date'],
            'dueDate' => ['required', 'date'],
            'currency' => ['required', 'string', 'max:10'],
            'vatRate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () {
            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'client_id' => $this->clientId,
                'invoice_number' => $this->invoiceNumber,
                'status' => 'draft',
                'issue_date' => $this->issueDate,
                'due_date' => $this->dueDate,
                'currency' => $this->currency,
                'notes' => $this->notes,
                'vat_rate' => $this->vatRate,
                'subtotal' => $this->subtotal,
                'vat_amount' => $this->vatAmount,
                'total' => $this->total,
                'vat_type' => $this->vatRate > 0 ? 'standard' : 'exempt',
                'language' => Auth::user()->locale ?: 'en',
                'template' => Auth::user()->currentCompany?->invoice_template ?? 'classic',
            ]);

            foreach ($this->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => (float) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                    'vat_rate' => $this->vatRate,
                    'total' => round((float) $item['quantity'] * (float) $item['unit_price'], 2),
                ]);
            }

            $this->import->update([
                'status' => 'completed',
                'invoice_id' => $invoice->id,
            ]);
        });

        $this->redirectAfterAction('invoices.index');
    }

    public function deleteImport(): void
    {
        $import = $this->import;

        if ($import->stored_path) {
            Storage::disk('minio')->delete($import->stored_path);
        }

        $import->delete();

        $this->redirectAfterAction('invoices.import');
    }

    public function skip(): void
    {
        $this->import->update(['status' => 'completed']);
        $this->redirectAfterAction('invoices.import');
    }

    public function render()
    {
        return view('livewire.invoices.import-review');
    }
}
