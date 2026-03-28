<?php

namespace App\Livewire\Invoices;

use App\Models\RecurringInvoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class RecurringInvoiceList extends Component
{
    use WithPagination;

    public function toggleActive(int $recurringId): void
    {
        $recurring = RecurringInvoice::where('user_id', Auth::id())->findOrFail($recurringId);
        $recurring->update(['active' => ! $recurring->active]);
    }

    public function delete(int $recurringId): void
    {
        $recurring = RecurringInvoice::where('user_id', Auth::id())->findOrFail($recurringId);
        $recurring->delete();
    }

    public function render()
    {
        $recurringInvoices = RecurringInvoice::where('user_id', Auth::id())
            ->with('client')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.invoices.recurring-invoice-list', compact('recurringInvoices'));
    }
}
