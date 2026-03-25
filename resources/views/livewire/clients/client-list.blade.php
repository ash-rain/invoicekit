<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Clients') }}</h2>
        <a href="{{ route('clients.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
            {{ __('+ New Client') }}
        </a>
    </div>

    <div class="mb-4">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="{{ __('Search clients...') }}"
            class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Country') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('VAT Number') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Currency') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($clients as $client)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $client->name }}</div>
                            <div class="text-sm text-gray-500">{{ $client->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $client->country }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $client->vat_number ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $client->currency }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('clients.edit', $client) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                            <button
                                wire:click="deleteClient({{ $client->id }})"
                                wire:confirm="{{ __('Delete this client?') }}"
                                class="text-red-600 hover:text-red-900"
                            >
                                {{ __('Delete') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">{{ __('No clients found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $clients->links() }}
    </div>
</div>
