<div class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm font-medium text-gray-500">{{ __('Tracked Hours (This Month)') }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $trackedHoursThisMonth }}h</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm font-medium text-gray-500">{{ __('Unpaid Invoices') }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $unpaidInvoicesCount }}</p>
            <p class="mt-1 text-sm text-gray-600">€{{ number_format($unpaidInvoicesTotal, 2) }} {{ __('outstanding') }}</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6 {{ $overdueInvoicesCount > 0 ? 'border-l-4 border-red-500' : '' }}">
            <p class="text-sm font-medium text-gray-500">{{ __('Overdue Invoices') }}</p>
            <p class="mt-2 text-3xl font-bold {{ $overdueInvoicesCount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                {{ $overdueInvoicesCount }}
            </p>
            @if($overdueInvoicesCount > 0)
                <p class="mt-1 text-sm text-red-500">{{ __('Requires attention') }}</p>
            @endif
        </div>
    </div>

    {{-- Overdue invoices widget --}}
    @if($overdueInvoices->isNotEmpty())
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-base font-semibold text-red-600">{{ __('⚠ Overdue Invoices') }}</h3>
            <a href="{{ route('invoices.index', ['statusFilter' => 'overdue']) }}" class="text-xs text-indigo-600 hover:text-indigo-800">{{ __('View all →') }}</a>
        </div>
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Invoice #') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Client') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Due') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($overdueInvoices as $inv)
                <tr class="hover:bg-red-50">
                    <td class="px-6 py-3 text-sm font-medium text-gray-900">
                        <a href="{{ route('invoices.show', $inv) }}" class="text-indigo-600 hover:underline">{{ $inv->invoice_number }}</a>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-700">{{ $inv->client->name }}</td>
                    <td class="px-6 py-3 text-sm text-red-600 font-medium">
                        {{ $inv->due_date->format('d M Y') }}
                        <span class="text-xs text-red-400">({{ $inv->due_date->diffInDays(now()) }}d ago)</span>
                    </td>
                    <td class="px-6 py-3 text-sm text-right font-medium text-gray-900">
                        {{ $inv->currency }} {{ number_format($inv->total, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
