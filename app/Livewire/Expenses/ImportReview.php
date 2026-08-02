<?php

namespace App\Livewire\Expenses;

use App\Models\Client;
use App\Models\DocumentImport;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ImportReview extends Component
{
    public DocumentImport $import;

    public string $description = '';

    public string $amount = '';

    public string $currency = 'EUR';

    public string $category = 'other';

    public string $date = '';

    public ?int $clientId = null;

    public ?int $projectId = null;

    public bool $billable = false;

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

        $this->description = $data['description'] ?? $data['vendor_name'] ?? '';
        $this->amount = (string) ($data['amount'] ?? $data['total'] ?? '');
        $this->currency = $data['currency'] ?? 'EUR';
        $this->category = $this->normalizeCategory($data['category'] ?? 'other');
        $this->date = $data['date'] ?? $data['issue_date'] ?? now()->format('Y-m-d');

        $this->clientId = $this->findMatchingClient($data);
    }

    private function normalizeCategory(string $raw): string
    {
        $allowed = ['software', 'hardware', 'travel', 'hosting', 'marketing', 'other'];
        $lower = strtolower($raw);

        return in_array($lower, $allowed) ? $lower : 'other';
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

    #[Computed]
    public function fileUrl(): ?string
    {
        return $this->import->stored_path
            ? Storage::disk('minio')->url($this->import->stored_path)
            : null;
    }

    #[Computed]
    public function clients()
    {
        return Client::where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function projects()
    {
        return Project::where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function queue()
    {
        return DocumentImport::where('user_id', Auth::id())
            ->where('document_type', 'expense')
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
        $this->redirect(route('expenses.import.review', $importId), navigate: true);
    }

    private function redirectAfterAction(string $fallbackRoute): void
    {
        $next = DocumentImport::where('user_id', Auth::id())
            ->where('document_type', 'expense')
            ->where('id', '!=', $this->import->id)
            ->where('status', 'extracted')
            ->orderBy('created_at')
            ->first();

        if ($next) {
            $this->redirect(route('expenses.import.review', $next), navigate: true);

            return;
        }

        $this->redirect(route($fallbackRoute), navigate: true);
    }

    public function confirm(): void
    {
        $this->validate([
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'max:10'],
            'category' => ['required', 'in:software,hardware,travel,hosting,marketing,other'],
            'date' => ['required', 'date'],
            'clientId' => ['nullable', 'integer', Rule::exists('clients', 'id')->where('user_id', Auth::id())],
            'projectId' => ['nullable', 'integer', Rule::exists('projects', 'id')->where('user_id', Auth::id())],
        ]);

        // Copy import file to receipts directory as receipt_file
        $receiptPath = null;
        if ($this->import->stored_path && Storage::disk('minio')->exists($this->import->stored_path)) {
            $ext = pathinfo($this->import->stored_path, PATHINFO_EXTENSION);
            $receiptPath = 'receipts/'.Auth::id().'/'.Str::uuid().'.'.$ext;
            Storage::disk('minio')->copy($this->import->stored_path, $receiptPath);
        }

        $expense = Expense::create([
            'user_id' => Auth::id(),
            'client_id' => $this->clientId,
            'project_id' => $this->projectId,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'category' => $this->category,
            'date' => $this->date,
            'billable' => $this->billable,
            'receipt_file' => $receiptPath,
        ]);

        $this->import->update([
            'status' => 'completed',
            'expense_id' => $expense->id,
        ]);

        $this->redirectAfterAction('expenses.index');
    }

    public function deleteImport(): void
    {
        $import = $this->import;

        if ($import->stored_path) {
            Storage::disk('minio')->delete($import->stored_path);
        }

        $import->delete();

        $this->redirectAfterAction('expenses.import');
    }

    public function skip(): void
    {
        $this->import->update(['status' => 'completed']);
        $this->redirectAfterAction('expenses.import');
    }

    public function render()
    {
        return view('livewire.expenses.import-review');
    }
}
