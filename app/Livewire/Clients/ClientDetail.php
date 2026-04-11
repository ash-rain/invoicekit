<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Services\InvoiceValidationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ClientDetail extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {
        if ($client->user_id !== Auth::id()) {
            abort(403);
        }

        $this->client = $client;
    }

    #[Computed]
    public function completeness(): array
    {
        $service = new InvoiceValidationService;
        $company = Auth::user()->currentCompany;
        $sellerCountry = $company?->country ?? 'BG';
        $validation = $service->clientCompleteness($this->client, $sellerCountry);

        return [
            'passes' => $validation->passes(),
            'errors' => $validation->errors(),
            'warnings' => $validation->warnings(),
        ];
    }

    #[Computed]
    public function recentInvoices()
    {
        return Invoice::where('user_id', Auth::id())
            ->where('client_id', $this->client->id)
            ->orderByDesc('issue_date')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function recentExpenses()
    {
        return Expense::where('user_id', Auth::id())
            ->where('client_id', $this->client->id)
            ->orderByDesc('date')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function totalInvoiced(): float
    {
        return (float) Invoice::where('user_id', Auth::id())
            ->where('client_id', $this->client->id)
            ->whereIn('status', ['sent', 'paid', 'overdue'])
            ->sum('total');
    }

    #[Computed]
    public function totalPaid(): float
    {
        return (float) Invoice::where('user_id', Auth::id())
            ->where('client_id', $this->client->id)
            ->where('status', 'paid')
            ->sum('total');
    }

    #[Computed]
    public function totalOutstanding(): float
    {
        return (float) Invoice::where('user_id', Auth::id())
            ->where('client_id', $this->client->id)
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('total');
    }

    #[Computed]
    public function totalExpenses(): float
    {
        return (float) Expense::where('user_id', Auth::id())
            ->where('client_id', $this->client->id)
            ->sum('amount');
    }

    public function render()
    {
        return view('livewire.clients.client-detail');
    }
}
