<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Invoices') }}</h2>
        <a href="{{ route('invoices.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
            {{ __('+ New Invoice') }}
        </a>
    </div>

    <div class="flex gap-3 mb-4">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="{{ __('Search by invoice number...') }}"
            class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <select
            wire:model.live="statusFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
            <option value="">{{ __('All statuses') }}</option>
            <option value="draft">{{ __('Draft') }}</option>
            <option value="sent">{{ __('Sent') }}</option>
            <option value="paid">{{ __('Paid') }}</option>
            <option value="overdue">{{ __('Overdue') }}</option>
        </select>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Invoice #') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Client') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Due Date') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $invoice->invoice_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $invoice->client->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $badgeClass = match($invoice->status) {
                                    'paid'    => 'bg-green-100 text-green-800',
                                    'sent'    => 'bg-blue-100 text-blue-800',
                                    'overdue' => 'bg-red-100 text-red-800',
                                    default   => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badgeClass }}">
                                {{ __(ucfirst($invoice->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $invoice->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                            {{ $invoice->due_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                            @if($invoice->status === 'draft')
                                <a href="{{ route('invoices.edit', $invoice) }}" class="text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                                <button
                                    wire:click="markSent({{ $invoice->id }})"
                                    wire:confirm="{{ __('Mark this invoice as sent?') }}"
                                    class="text-blue-600 hover:text-blue-900"
                                >{{ __('Send') }}</button>
                            @endif
                            @if(in_array($invoice->status, ['sent', 'overdue']))
                                <button
                                    wire:click="markPaid({{ $invoice->id }})"
                                    wire:confirm="{{ __('Mark this invoice as paid?') }}"
                                    class="text-green-600 hover:text-green-900"
                                >{{ __('Mark Paid') }}</button>
                            @endif
                            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="text-gray-600 hover:text-gray-900">{{ __('PDF') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">{{ __('No invoices found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>
</div>
