<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public int $trackedHoursThisMonth = 0;
    public int $unpaidInvoicesCount = 0;
    public float $unpaidInvoicesTotal = 0;
    public int $overdueInvoicesCount = 0;

    public function mount(): void
    {
        $userId = Auth::id();

        $this->trackedHoursThisMonth = (int) ceil(
            TimeEntry::where('user_id', $userId)
                ->whereMonth('started_at', now()->month)
                ->whereYear('started_at', now()->year)
                ->whereNotNull('duration_minutes')
                ->sum('duration_minutes') / 60
        );

        $unpaidInvoices = Invoice::where('user_id', $userId)->unpaid()->get();
        $this->unpaidInvoicesCount = $unpaidInvoices->count();
        $this->unpaidInvoicesTotal = $unpaidInvoices->sum('total');

        $this->overdueInvoicesCount = Invoice::where('user_id', $userId)->overdue()->count();
    }

    public function render()
    {
        $overdueInvoices = Invoice::where('user_id', Auth::id())
            ->overdue()
            ->with('client')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return view('livewire.dashboard', compact('overdueInvoices'));
    }
}
