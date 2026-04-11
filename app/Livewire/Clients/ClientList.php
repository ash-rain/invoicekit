<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Services\InvoiceValidationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ClientList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteClient(int $clientId): void
    {
        $client = Client::where('user_id', Auth::id())->findOrFail($clientId);
        $client->delete();
        $this->dispatch('client-deleted');
    }

    #[Computed]
    public function clientCompleteness(): array
    {
        $service = new InvoiceValidationService;
        $company = Auth::user()->currentCompany;
        $sellerCountry = $company?->country ?? 'BG';
        $result = [];

        $clients = Client::where('user_id', Auth::id())
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        foreach ($clients as $client) {
            $validation = $service->clientCompleteness($client, $sellerCountry);
            $result[$client->id] = [
                'passes' => $validation->passes(),
                'errors' => $validation->errors(),
                'warnings' => $validation->warnings(),
            ];
        }

        return $result;
    }

    public function render()
    {
        $clients = Client::where('user_id', Auth::id())
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.clients.client-list', compact('clients'));
    }
}
