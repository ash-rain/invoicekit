<div class="p-6 lg:p-8">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">
        <div>
            <h1 class="text-[26px] font-bold text-gray-900 leading-tight" style="font-family:'Syne',sans-serif;letter-spacing:-0.025em;">{{ __('Clients') }}</h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ __('Manage your client accounts') }}</p>
        </div>
        <a
            href="{{ route('clients.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all self-start sm:self-auto"
            style="background:#0f1117;color:white;"
            onmouseover="this.style.background='#1e2130'" onmouseout="this.style.background='#0f1117'"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Client') }}
        </a>
    </div>

    {{-- Search --}}
    <div class="mb-5">
        <div class="relative max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:#9ca3af;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="{{ __('Search clients...') }}"
                class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                style="background:white;border:1px solid #e5e7eb;color:#111827;"
            >
        </div>
    </div>

    {{-- Table card --}}
    <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #eaecf0;">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr style="border-bottom:1px solid #f3f4f6;background:#fafafa;">
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Client') }}</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Country') }}</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('VAT Number') }}</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Currency') }}</th>
                        <th class="px-5 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr style="border-top:1px solid #f3f4f6;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                                        {{ strtoupper(substr($client->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $client->name }}</p>
                                        <p class="text-xs text-gray-400 truncate">{{ $client->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $client->country }}</td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($client->vat_number)
                                    <span class="text-xs font-mono font-medium text-gray-700 px-2 py-1 rounded-lg" style="background:#f3f4f6;">{{ $client->vat_number }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-700 whitespace-nowrap">{{ $client->currency }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('clients.edit', $client) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900 transition-colors">{{ __('Edit') }}</a>
                                    <button
                                        wire:click="deleteClient({{ $client->id }})"
                                        wire:confirm="{{ __('Delete this client?') }}"
                                        class="text-xs font-semibold text-red-500 hover:text-red-700 transition-colors"
                                    >{{ __('Delete') }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-1" style="background:#f3f4f6;">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600">{{ __('No clients found') }}</p>
                                    <p class="text-xs text-gray-400">{{ __('Add your first client to get started') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($clients->hasPages())
    <div class="mt-5">
        {{ $clients->links() }}
    </div>
    @endif

</div>
