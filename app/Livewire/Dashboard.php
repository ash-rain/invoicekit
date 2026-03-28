<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
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

    public float $revenueThisMonth = 0;

    public int $totalClients = 0;

    public int $activeProjects = 0;

    public float $expensesThisMonth = 0;

    public string $defaultCurrency = 'EUR';

    public function mount(): void
    {
        $userId = Auth::id();
        $user = Auth::user();

        if ($user->currentCompany) {
            $this->defaultCurrency = $user->currentCompany->default_currency ?? 'EUR';
        }

        $this->trackedHoursThisMonth = (int) ceil(
            TimeEntry::where('user_id', $userId)
                ->whereMonth('started_at', now()->month)
                ->whereYear('started_at', now()->year)
                ->whereNotNull('duration_minutes')
                ->sum('duration_minutes') / 60
        );

        $unpaidInvoices = Invoice::where('user_id', $userId)->unpaid()->get();
        $this->unpaidInvoicesCount = $unpaidInvoices->count();
        $this->unpaidInvoicesTotal = (float) $unpaidInvoices->sum('total');

        $this->overdueInvoicesCount = Invoice::where('user_id', $userId)->overdue()->count();

        $this->revenueThisMonth = (float) Invoice::where('user_id', $userId)
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        $this->totalClients = Client::where('user_id', $userId)->count();

        $this->activeProjects = Project::where('user_id', $userId)->active()->count();

        $this->expensesThisMonth = (float) Expense::where('user_id', $userId)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
    }

    public function render()
    {
        $userId = Auth::id();

        $overdueInvoices = Invoice::where('user_id', $userId)
            ->overdue()
            ->with('client')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $recentInvoices = Invoice::where('user_id', $userId)
            ->with('client')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $upcomingInvoices = Invoice::where('user_id', $userId)
            ->where('status', 'sent')
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->with('client')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('livewire.dashboard', compact('overdueInvoices', 'recentInvoices', 'upcomingInvoices'));
    }
}
