<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use App\Notifications\InvoicePaidNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class InvoiceList extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public string $documentTypeFilter = '';

    public string $search = '';

    #[Url]
    public string $clientFilter = '';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDocumentTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingClientFilter(): void
    {
        $this->resetPage();
    }

    public function markSent(int $invoiceId): void
    {
        $invoice = Invoice::where('user_id', Auth::id())->findOrFail($invoiceId);
        if ($invoice->status === 'draft') {
            $invoice->update(['status' => 'sent']);
        }
    }

    public function markPaid(int $invoiceId): void
    {
        $invoice = Invoice::where('user_id', Auth::id())->with('client')->findOrFail($invoiceId);
        if (in_array($invoice->status, ['sent', 'overdue'])) {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            Auth::user()->notify(new InvoicePaidNotification($invoice));
        }
    }

    public function cancelInvoice(int $invoiceId, string $reason = ''): void
    {
        $invoice = Invoice::where('user_id', Auth::id())->findOrFail($invoiceId);
        $invoice->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason ?: null,
        ]);
    }

    public function annulInvoice(int $invoiceId, string $reason = ''): void
    {
        $invoice = Invoice::where('user_id', Auth::id())
            ->with(['items'])
            ->findOrFail($invoiceId);

        // Can only annul sent/overdue invoices that aren't already cancelled
        if ($invoice->isCancelled() || ! in_array($invoice->status, ['sent', 'overdue'])) {
            return;
        }

        $user = Auth::user();
        $company = $user->currentCompany;

        // Generate next invoice number (shared BG fiscal sequence)
        $creditNoteNumber = Invoice::generateNumber($user->id, $company);

        $correctionReason = 'Анулиране на фактура №'.$invoice->invoice_number;
        if ($reason) {
            $correctionReason .= ' — '.$reason;
        }

        // Create credit note for full amount
        $creditNote = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $invoice->client_id,
            'invoice_number' => $creditNoteNumber,
            'status' => 'draft',
            'document_type' => 'credit_note',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'payment_due_date' => now()->toDateString(),
            'currency' => $invoice->currency,
            'subtotal' => $invoice->subtotal,
            'vat_rate' => $invoice->vat_rate,
            'vat_amount' => $invoice->vat_amount,
            'total' => $invoice->total,
            'vat_type' => $invoice->vat_type,
            'language' => $invoice->language,
            'original_invoice_id' => $invoice->id,
            'original_invoice_number' => $invoice->invoice_number,
            'original_invoice_date' => $invoice->issue_date,
            'correction_reason' => $correctionReason,
            'payment_method_id' => $invoice->payment_method_id,
            'payment_method_snapshot' => $invoice->payment_method_snapshot,
        ]);

        // Copy line items to credit note
        foreach ($invoice->items as $item) {
            $creditNote->items()->create([
                'description' => $item->description,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'vat_rate' => $item->vat_rate,
                'vat_rate_key' => $item->vat_rate_key,
                'total' => $item->total,
            ]);
        }

        // Mark original as cancelled
        $invoice->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $correctionReason,
        ]);
    }

    #[Computed]
    public function clients()
    {
        return Client::where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        $invoices = Invoice::where('user_id', Auth::id())
            ->with('client')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->documentTypeFilter, fn ($q) => $q->where('document_type', $this->documentTypeFilter))
            ->when($this->clientFilter, fn ($q) => $q->where('client_id', $this->clientFilter))
            ->when($this->search, fn ($q) => $q->where('invoice_number', 'like', "%{$this->search}%"))
            ->orderByDesc('issue_date')
            ->paginate(15);

        return view('livewire.invoices.invoice-list', compact('invoices'));
    }
}
