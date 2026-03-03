<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

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

    public function render()
    {
        $clients = Client::where('user_id', Auth::id())
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.clients.client-list', compact('clients'));
    }
}
