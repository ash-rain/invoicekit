<div class="p-6 lg:p-8">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">
        <div>
            <h1 class="text-[26px] font-bold text-gray-900 leading-tight"
                style="font-family:'Syne',sans-serif;letter-spacing:-0.025em;">
                {{ __('Expenses') }}
            </h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ __('Track what you spend on clients and projects') }}</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('expenses.export') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                {{ __('CSV Export') }}
            </a>
            <a href="{{ route('expenses.import') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347A3.75 3.75 0 0113.5 21h-3a3.75 3.75 0 01-2.652-1.098l-.347-.347z"/>
                </svg>
                {{ __('Import Expenses') }}
            </a>
            <a href="{{ route('expenses.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all"
                style="background:#0f1117;color:white;" onmouseover="this.style.background='#1e2130'"
                onmouseout="this.style.background='#0f1117'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('New Expense') }}
            </a>
        </div>
    </div>

    {{-- Monthly summary + filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-5">
        <div class="flex-1 bg-white rounded-2xl px-5 py-4" style="border:1px solid #eaecf0;">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">{{ __('This Month') }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency('EUR', (float) $monthlySummary) }}</p>
        </div>
        <div class="flex items-center gap-2 sm:ml-auto">
            <input wire:model.live="search" type="search" placeholder="{{ __('Search expenses…') }}"
                class="px-3 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none w-48">
            <select wire:model.live="categoryFilter"
                class="px-3 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">{{ __('All categories') }}</option>
                <option value="software">{{ __('Software') }}</option>
                <option value="hardware">{{ __('Hardware') }}</option>
                <option value="travel">{{ __('Travel') }}</option>
                <option value="hosting">{{ __('Hosting') }}</option>
                <option value="marketing">{{ __('Marketing') }}</option>
                <option value="other">{{ __('Other') }}</option>
            </select>
            <select wire:model.live="clientFilter"
                class="px-3 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">{{ __('All Clients') }}</option>
                @foreach ($this->clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #eaecf0;">

        @if ($expenses->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-sm font-semibold text-gray-500">{{ __('No expenses yet') }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ __('Add your first expense to start tracking spending.') }}
                </p>
            </div>
        @else
            <table class="min-w-full">
                <thead style="background:#fafafa;border-bottom:1px solid #f3f4f6;">
                    <tr>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            {{ __('Date') }}</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            {{ __('Description') }}</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            {{ __('Category') }}</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            {{ __('Client') }}</th>
                        <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            {{ __('Amount') }}</th>
                        <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            {{ __('Billable') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenses as $expense)
                        <tr class="border-t border-[#f3f4f6] hover:bg-[#fafafa] transition"
                            wire:key="expense-{{ $expense->id }}">
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-600">{{ $expense->date->format('d M Y') }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-semibold text-gray-900">{{ $expense->description }}</span>
                                @if ($expense->receipt_file)
                                    <a href="{{ $expense->receiptUrl() }}" target="_blank"
                                        class="ml-1.5 text-xs text-indigo-500 hover:text-indigo-700">{{ __('Receipt') }}</a>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 capitalize">
                                    {{ __($expense->category) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-600">{{ $expense->client?->name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span
                                    class="text-sm font-semibold text-gray-800">{{ formatCurrency($expense->currency, (float) $expense->amount) }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if ($expense->billable)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-700">{{ __('Billable') }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('expenses.edit', $expense) }}"
                                        class="text-xs text-gray-500 hover:text-gray-700 font-medium">{{ __('Edit') }}</a>
                                    <button wire:click="delete({{ $expense->id }})"
                                        wire:confirm="{{ __('Delete this expense?') }}"
                                        class="text-xs text-red-400 hover:text-red-600 font-medium">{{ __('Delete') }}</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($expenses->hasPages())
                <div class="px-5 py-4 border-t border-[#f3f4f6]">
                    {{ $expenses->links() }}
                </div>
            @endif
        @endif

    </div>
</div>
