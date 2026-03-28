<div class="p-6 lg:p-8">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">
        <div>
            <h1 class="text-[26px] font-bold text-gray-900 leading-tight" style="font-family:'Syne',sans-serif;letter-spacing:-0.025em;">
                {{ __('Recurring Invoices') }}
            </h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ __('Auto-generate invoices on a schedule') }}</p>
        </div>
        <a href="{{ route('recurring-invoices.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all self-start sm:self-auto"
            style="background:#0f1117;color:white;"
            onmouseover="this.style.background='#1e2130'" onmouseout="this.style.background='#0f1117'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Template') }}
        </a>
    </div>

    {{-- Table card --}}
    <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #eaecf0;">

        @if ($recurringInvoices->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <p class="text-sm font-semibold text-gray-500">{{ __('No recurring invoices yet') }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ __('Create a template to auto-generate invoices on a schedule.') }}</p>
            </div>
        @else
            <table class="min-w-full">
                <thead style="background:#fafafa;border-bottom:1px solid #f3f4f6;">
                    <tr>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Client') }}</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Frequency') }}</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Next Send') }}</th>
                        <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Total') }}</th>
                        <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recurringInvoices as $recurring)
                        <tr class="border-t border-[#f3f4f6] hover:bg-[#fafafa] transition" wire:key="recurring-{{ $recurring->id }}">
                            <td class="px-5 py-4">
                                <span class="text-sm font-semibold text-gray-900">{{ $recurring->client->name }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-600 capitalize">{{ __($recurring->frequency) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-600">{{ $recurring->next_send_date->format('d M Y') }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-sm font-semibold text-gray-800">{{ formatCurrency($recurring->currency, (float) $recurring->total) }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button wire:click="toggleActive({{ $recurring->id }})"
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full transition {{ $recurring->active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                    {{ $recurring->active ? __('Active') : __('Paused') }}
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('recurring-invoices.edit', $recurring) }}"
                                        class="text-xs text-gray-500 hover:text-gray-700 font-medium">{{ __('Edit') }}</a>
                                    <button wire:click="delete({{ $recurring->id }})"
                                        wire:confirm="{{ __('Delete this recurring template?') }}"
                                        class="text-xs text-red-400 hover:text-red-600 font-medium">{{ __('Delete') }}</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($recurringInvoices->hasPages())
                <div class="px-5 py-4 border-t border-[#f3f4f6]">
                    {{ $recurringInvoices->links() }}
                </div>
            @endif
        @endif

    </div>
</div>
