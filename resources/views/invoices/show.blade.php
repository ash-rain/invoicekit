<x-app-layout>
    <x-slot name="header">Invoice {{ $invoice->invoice_number }}</x-slot>

    @php
        $invoice = \App\Models\Invoice::with(['client', 'items', 'user'])->findOrFail($invoice);
        $lang = $invoice->language ?? 'en';
    @endphp

    <div class="max-w-3xl mx-auto space-y-4">

        {{-- Status bar --}}
        <div class="bg-white rounded-xl shadow p-4 flex items-center justify-between">
            @php
                $badgeClass = match($invoice->status) {
                    'paid'    => 'bg-green-100 text-green-800',
                    'sent'    => 'bg-blue-100 text-blue-800',
                    'overdue' => 'bg-red-100 text-red-800',
                    default   => 'bg-gray-100 text-gray-800',
                };
            @endphp
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $badgeClass }}">
                    {{ ucfirst($invoice->status) }}
                </span>
                <span class="text-sm text-gray-500">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="flex items-center gap-2">
                @if($invoice->status === 'draft')
                    <a href="{{ route('invoices.edit', $invoice) }}"
                       class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Edit
                    </a>
                @endif
                <a href="{{ route('invoices.pdf', $invoice) }}"
                   target="_blank"
                   class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Download PDF
                </a>
            </div>
        </div>

        {{-- Invoice detail card --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-5">

            {{-- Parties --}}
            <div class="grid grid-cols-2 gap-6 pb-5 border-b border-gray-100">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">From</p>
                    <p class="text-sm font-medium text-gray-900">{{ $invoice->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $invoice->user->email }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Bill To</p>
                    <p class="text-sm font-medium text-gray-900">{{ $invoice->client->name }}</p>
                    @if($invoice->client->address)
                        <p class="text-xs text-gray-500 whitespace-pre-line">{{ $invoice->client->address }}</p>
                    @endif
                    @if($invoice->client->vat_number)
                        <p class="text-xs text-gray-500 font-mono">VAT: {{ $invoice->client->vat_number }}</p>
                    @endif
                </div>
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-3 gap-4 pb-5 border-b border-gray-100">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Issue Date</p>
                    <p class="text-sm text-gray-900">{{ $invoice->issue_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Due Date</p>
                    <p class="text-sm {{ $invoice->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                    </p>
                </div>
                @if($invoice->paid_at)
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Paid On</p>
                    <p class="text-sm text-green-600 font-medium">{{ $invoice->paid_at->format('d M Y') }}</p>
                </div>
                @endif
            </div>

            {{-- Items --}}
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-500 uppercase">
                        <th class="pb-2 text-left font-medium">Description</th>
                        <th class="pb-2 text-right font-medium w-16">Qty</th>
                        <th class="pb-2 text-right font-medium w-24">Unit Price</th>
                        <th class="pb-2 text-right font-medium w-24">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="py-2 text-gray-800">{{ $item->description }}</td>
                        <td class="py-2 text-right text-gray-600">{{ rtrim(rtrim(number_format((float)$item->quantity, 2), '0'), '.') }}</td>
                        <td class="py-2 text-right text-gray-600">{{ formatCurrency($invoice->currency, (float)$item->unit_price) }}</td>
                        <td class="py-2 text-right text-gray-800 font-medium">{{ formatCurrency($invoice->currency, $item->subtotal()) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="flex justify-end pt-2 border-t border-gray-100">
                <div class="w-64 space-y-1 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>{{ formatCurrency($invoice->currency, (float)$invoice->subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>VAT @if($invoice->vat_rate > 0)({{ $invoice->vat_rate }}%)@endif</span>
                        <span>{{ formatCurrency($invoice->currency, (float)$invoice->vat_amount) }}</span>
                    </div>
                    @if($invoice->vat_type && $invoice->vat_type !== 'standard')
                        <p class="text-xs text-amber-700 bg-amber-50 rounded px-2 py-1">
                            @if($invoice->vat_type === 'reverse_charge') VAT Reverse Charge
                            @elseif($invoice->vat_type === 'oss') OSS Scheme
                            @elseif($invoice->vat_type === 'exempt') VAT Exempt
                            @endif
                        </p>
                    @endif
                    <div class="flex justify-between text-gray-900 font-bold text-base pt-1 border-t border-gray-200">
                        <span>Total</span>
                        <span>{{ formatCurrency($invoice->currency, (float)$invoice->total) }}</span>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if($invoice->notes)
            <div class="pt-2 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Notes</p>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $invoice->notes }}</p>
            </div>
            @endif

        </div>

        <div class="text-center">
            <a href="{{ route('invoices.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to invoices</a>
        </div>

    </div>
</x-app-layout>
