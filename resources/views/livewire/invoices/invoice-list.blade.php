<div class="p-6 lg:p-8">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">
        <div>
            <h1 class="text-[26px] font-bold text-gray-900 leading-tight" style="font-family:'Syne',sans-serif;letter-spacing:-0.025em;">{{ __('Invoices') }}</h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ __('Create and manage your invoices') }}</p>
        </div>
        <a
            href="{{ route('invoices.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all self-start sm:self-auto"
            style="background:#0f1117;color:white;"
            onmouseover="this.style.background='#1e2130'" onmouseout="this.style.background='#0f1117'"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Invoice') }}
        </a>
    </div>

    {{-- Filter bar --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1 max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:#9ca3af;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="{{ __('Search invoice number...') }}"
                class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                style="background:white;border:1px solid #e5e7eb;color:#111827;"
            >
        </div>
        <select
            wire:model.live="statusFilter"
            class="px-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
            style="background:white;border:1px solid #e5e7eb;color:#374151;"
        >
            <option value="">{{ __('All statuses') }}</option>
            <option value="draft">{{ __('Draft') }}</option>
            <option value="sent">{{ __('Sent') }}</option>
            <option value="paid">{{ __('Paid') }}</option>
            <option value="overdue">{{ __('Overdue') }}</option>
        </select>
    </div>

    {{-- Table card --}}
    <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #eaecf0;">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr style="border-bottom:1px solid #f3f4f6;background:#fafafa;">
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Invoice #') }}</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Client') }}</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Status') }}</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total') }}</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Due Date') }}</th>
                        <th class="px-5 py-3.5 text-right text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        @php
                            $badgeDot = match($invoice->status) {
                                'paid'    => 'background:#22c55e',
                                'sent'    => 'background:#3b82f6',
                                'overdue' => 'background:#ef4444',
                                default   => 'background:#9ca3af',
                            };
                            $badgeStyle = match($invoice->status) {
                                'paid'    => 'background:#f0fdf4;color:#15803d;',
                                'sent'    => 'background:#eff6ff;color:#1d4ed8;',
                                'overdue' => 'background:#fef2f2;color:#dc2626;',
                                default   => 'background:#f9fafb;color:#6b7280;',
                            };
                        @endphp
                        <tr style="border-top:1px solid #f3f4f6;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                            <td class="px-5 py-4 text-sm font-semibold text-gray-900 whitespace-nowrap">
                                {{ $invoice->invoice_number }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 whitespace-nowrap">
                                {{ $invoice->client->name }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold" style="{{ $badgeStyle }}">
                                    <span class="w-1.5 h-1.5 rounded-full inline-block shrink-0" style="{{ $badgeDot }}"></span>
                                    {{ __(ucfirst($invoice->status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 whitespace-nowrap font-medium">
                                {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap {{ $invoice->isOverdue() ? 'font-semibold' : 'text-gray-600' }}" style="{{ $invoice->isOverdue() ? 'color:#dc2626;' : '' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900 transition-colors">{{ __('View') }}</a>
                                    @if($invoice->status === 'draft')
                                        <a href="{{ route('invoices.edit', $invoice) }}" class="text-xs font-semibold text-gray-500 hover:text-gray-800 transition-colors">{{ __('Edit') }}</a>
                                        <button wire:click="markSent({{ $invoice->id }})" wire:confirm="{{ __('Mark this invoice as sent?') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-900 transition-colors">{{ __('Send') }}</button>
                                    @endif
                                    @if(in_array($invoice->status, ['sent', 'overdue']))
                                        <button wire:click="markPaid({{ $invoice->id }})" wire:confirm="{{ __('Mark this invoice as paid?') }}" class="text-xs font-semibold text-green-600 hover:text-green-900 transition-colors">{{ __('Mark Paid') }}</button>
                                    @endif
                                    <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="text-xs font-semibold text-gray-500 hover:text-gray-800 transition-colors">{{ __('PDF') }}</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-1" style="background:#f3f4f6;">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600">{{ __('No invoices found') }}</p>
                                    <p class="text-xs text-gray-400">{{ __('Create your first invoice to get started') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($invoices->hasPages())
    <div class="mt-5">
        {{ $invoices->links() }}
    </div>
    @endif

</div>
