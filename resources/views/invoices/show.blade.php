<x-app-layout>
    @php
        $invoice = \App\Models\Invoice::with(['client', 'items', 'user'])->findOrFail($invoice);
        $lang = $invoice->language ?? 'en';
    @endphp

    <div class="p-6 max-w-3xl mx-auto">
        {{-- Back + Page header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <a href="{{ route('invoices.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ __('Back to Invoices') }}
                </a>
                <h1 class="font-bold text-[26px] text-[#0f1117] tracking-tight" style="font-family:'Syne',sans-serif;">
                    {{ $invoice->invoice_number }}
                </h1>
            </div>
            <div class="flex items-center gap-2">
                @if($invoice->status === 'draft')
                    <a href="{{ route('invoices.edit', $invoice) }}"
                       class="px-4 py-2.5 text-sm font-semibold border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                        {{ __('Edit') }}
                    </a>
                @endif
                <a href="{{ route('invoices.pdf', $invoice) }}"
                   target="_blank"
                   class="px-4 py-2.5 text-sm font-bold bg-[#0f1117] text-white rounded-xl hover:bg-[#1a1f2e]">
                    {{ __('Download PDF') }}
                </a>
            </div>
        </div>

        {{-- Status bar --}}
        @php
            $badgeColor = match($invoice->status) {
                'paid'    => 'bg-green-100 text-green-800',
                'sent'    => 'bg-blue-100 text-blue-800',
                'overdue' => 'bg-red-100 text-red-800',
                default   => 'bg-gray-100 text-gray-700',
            };
        @endphp
        <div class="mb-6">
            <span class="inline-flex items-center px-3 py-1 text-sm font-bold rounded-full {{ $badgeColor }}">
                {{ ucfirst($invoice->status) }}
            </span>
        </div>

        {{-- Invoice detail card --}}
        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 space-y-6">

            {{-- Parties --}}
            <div class="grid grid-cols-2 gap-6 pb-6 border-b border-[#f3f4f6]">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('From') }}</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $invoice->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $invoice->user->email }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Bill To') }}</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $invoice->client->name }}</p>
                    @if($invoice->client->address)
                        <p class="text-xs text-gray-500 whitespace-pre-line">{{ $invoice->client->address }}</p>
                    @endif
                    @if($invoice->client->vat_number)
                        <p class="text-xs text-gray-500 font-mono">{{ __('VAT:') }} {{ $invoice->client->vat_number }}</p>
                    @endif
                </div>
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-3 gap-4 pb-6 border-b border-[#f3f4f6]">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Issue Date') }}</p>
                    <p class="text-sm text-gray-900">{{ $invoice->issue_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Due Date') }}</p>
                    <p class="text-sm {{ $invoice->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                    </p>
                </div>
                @if($invoice->paid_at)
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Paid On') }}</p>
                        <p class="text-sm text-green-600 font-semibold">{{ $invoice->paid_at->format('d M Y') }}</p>
                    </div>
                @endif
            </div>

            {{-- Items --}}
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-[#fafafa]">
                        <th class="py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Description') }}</th>
                        <th class="py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider w-16">{{ __('Qty') }}</th>
                        <th class="py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider w-28">{{ __('Unit Price') }}</th>
                        <th class="py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider w-28">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr class="border-t border-[#f3f4f6]">
                            <td class="py-3 text-gray-800">{{ $item->description }}</td>
                            <td class="py-3 text-right text-gray-600">{{ rtrim(rtrim(number_format((float)$item->quantity, 2), '0'), '.') }}</td>
                            <td class="py-3 text-right text-gray-600">{{ formatCurrency($invoice->currency, (float)$item->unit_price) }}</td>
                            <td class="py-3 text-right text-gray-800 font-semibold">{{ formatCurrency($invoice->currency, $item->subtotal()) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="flex justify-end border-t border-[#f3f4f6] pt-4">
                <div class="w-64 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __('Subtotal') }}</span>
                        <span>{{ formatCurrency($invoice->currency, (float)$invoice->subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __('VAT') }} @if($invoice->vat_rate > 0)({{ $invoice->vat_rate }}%)@endif</span>
                        <span>{{ formatCurrency($invoice->currency, (float)$invoice->vat_amount) }}</span>
                    </div>
                    @if($invoice->vat_type && $invoice->vat_type !== 'standard')
                        <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-2.5 py-1.5">
                            @if($invoice->vat_type === 'reverse_charge') {{ __('VAT Reverse Charge') }}
                            @elseif($invoice->vat_type === 'oss') {{ __('OSS Scheme') }}
                            @elseif($invoice->vat_type === 'exempt') {{ __('VAT Exempt') }}
                            @endif
                        </p>
                    @endif
                    <div class="flex justify-between text-[#0f1117] font-bold text-base pt-2 border-t border-[#eaecf0]">
                        <span>{{ __('Total') }}</span>
                        <span>{{ formatCurrency($invoice->currency, (float)$invoice->total) }}</span>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if($invoice->notes)
                <div class="pt-4 border-t border-[#f3f4f6]">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('Notes') }}</p>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
