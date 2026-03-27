<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Notifications\InvoicePaidNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class InvoiceList extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public string $search = '';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
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

    public function render()
    {
        $invoices = Invoice::where('user_id', Auth::id())
            ->with('client')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn ($q) => $q->where('invoice_number', 'like', "%{$this->search}%"))
            ->orderByDesc('issue_date')
            ->paginate(15);

        return view('livewire.invoices.invoice-list', compact('invoices'));
    }
}
