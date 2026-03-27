<div class="p-6 lg:p-8">

    {{-- Page header --}}
    <div class="mb-8">
        <h1 class="text-[26px] font-bold text-gray-900 leading-tight" style="font-family:'Syne',sans-serif;letter-spacing:-0.025em;">{{ __('Dashboard') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Welcome back') }}, {{ auth()->user()->name }}</p>
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

        {{-- Tracked hours --}}
        <div class="bg-white rounded-2xl p-6 overflow-hidden" style="border:1px solid #eaecf0;">
            <div class="flex items-start justify-between mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(99,102,241,0.1);">
                    <svg style="width:18px;height:18px;color:#6366f1;" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#9ca3af;letter-spacing:0.07em;">{{ __('Tracked This Month') }}</p>
            <p class="font-bold leading-none text-gray-900" style="font-family:'Syne',sans-serif;font-size:2.5rem;letter-spacing:-0.03em;">
                {{ $trackedHoursThisMonth }}<span class="text-2xl font-semibold text-gray-400 ml-0.5">h</span>
            </p>
        </div>

        {{-- Unpaid invoices --}}
        <div class="bg-white rounded-2xl p-6 overflow-hidden" style="border:1px solid #eaecf0;">
            <div class="flex items-start justify-between mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(245,158,11,0.1);">
                    <svg style="width:18px;height:18px;color:#f59e0b;" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#9ca3af;letter-spacing:0.07em;">{{ __('Unpaid Invoices') }}</p>
            <p class="font-bold leading-none text-gray-900" style="font-family:'Syne',sans-serif;font-size:2.5rem;letter-spacing:-0.03em;">{{ $unpaidInvoicesCount }}</p>
            <p class="mt-2 text-sm text-gray-500">€{{ number_format($unpaidInvoicesTotal, 2) }} {{ __('outstanding') }}</p>
        </div>

        {{-- Overdue --}}
        <div class="rounded-2xl p-6 overflow-hidden" style="border:1px solid {{ $overdueInvoicesCount > 0 ? '#fecaca' : '#eaecf0' }};background:{{ $overdueInvoicesCount > 0 ? '#fff8f8' : 'white' }};">
            <div class="flex items-start justify-between mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $overdueInvoicesCount > 0 ? 'rgba(239,68,68,0.1)' : 'rgba(34,197,94,0.1)' }};">
                    @if($overdueInvoicesCount > 0)
                        <svg style="width:18px;height:18px;color:#ef4444;" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    @else
                        <svg style="width:18px;height:18px;color:#22c55e;" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:{{ $overdueInvoicesCount > 0 ? '#f87171' : '#9ca3af' }};letter-spacing:0.07em;">{{ __('Overdue') }}</p>
            <p class="font-bold leading-none" style="font-family:'Syne',sans-serif;font-size:2.5rem;letter-spacing:-0.03em;color:{{ $overdueInvoicesCount > 0 ? '#dc2626' : '#111827' }};">{{ $overdueInvoicesCount }}</p>
            @if($overdueInvoicesCount > 0)
                <p class="mt-2 text-sm font-medium" style="color:#ef4444;">{{ __('Requires attention') }}</p>
            @else
                <p class="mt-2 text-sm font-medium" style="color:#22c55e;">{{ __('All caught up!') }}</p>
            @endif
        </div>

    </div>

    {{-- Overdue invoices table --}}
    @if($overdueInvoices->isNotEmpty())
    <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #fecaca;">
        <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid #fef2f2;">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse inline-block"></span>
                <h3 class="text-sm font-bold text-red-600">{{ __('Overdue Invoices') }}</h3>
            </div>
            <a href="{{ route('invoices.index', ['statusFilter' => 'overdue']) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                {{ __('View all') }} →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr style="background:#fef2f2;">
                        <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider" style="color:#f87171;letter-spacing:0.07em;">{{ __('Invoice #') }}</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider" style="color:#f87171;letter-spacing:0.07em;">{{ __('Client') }}</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider" style="color:#f87171;letter-spacing:0.07em;">{{ __('Due Date') }}</th>
                        <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider" style="color:#f87171;letter-spacing:0.07em;">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdueInvoices as $inv)
                    <tr class="transition-colors" style="border-top:1px solid #fef2f2;" onmouseover="this.style.background='#fff8f8'" onmouseout="this.style.background=''">
                        <td class="px-6 py-3.5 text-sm font-semibold">
                            <a href="{{ route('invoices.show', $inv) }}" class="text-indigo-600 hover:text-indigo-800 transition-colors">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="px-6 py-3.5 text-sm text-gray-700">{{ $inv->client->name }}</td>
                        <td class="px-6 py-3.5">
                            <span class="text-sm font-medium text-red-600">{{ $inv->due_date->format('d M Y') }}</span>
                            <span class="ml-2 text-xs" style="color:#f87171;">{{ $inv->due_date->diffInDays(now()) }}d {{ __('ago') }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-sm font-bold text-right text-gray-900">{{ $inv->currency }} {{ number_format($inv->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
