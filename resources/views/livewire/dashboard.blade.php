<div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-sm font-medium text-gray-500">Tracked Hours (This Month)</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $trackedHoursThisMonth }}h</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-sm font-medium text-gray-500">Unpaid Invoices</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $unpaidInvoicesCount }}</p>
        <p class="mt-1 text-sm text-gray-600">€{{ number_format($unpaidInvoicesTotal, 2) }} outstanding</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6 {{ $overdueInvoicesCount > 0 ? 'border-l-4 border-red-500' : '' }}">
        <p class="text-sm font-medium text-gray-500">Overdue Invoices</p>
        <p class="mt-2 text-3xl font-bold {{ $overdueInvoicesCount > 0 ? 'text-red-600' : 'text-gray-900' }}">
            {{ $overdueInvoicesCount }}
        </p>
        @if($overdueInvoicesCount > 0)
            <p class="mt-1 text-sm text-red-500">Requires attention</p>
        @endif
    </div>
</div>
