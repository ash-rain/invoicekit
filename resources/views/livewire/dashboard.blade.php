<div class="p-6 lg:p-8 space-y-6">

    {{-- ─── Page header ─────────────────────────────────────────── --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-[26px] font-bold text-gray-900 leading-tight"
                style="font-family:'Syne',sans-serif;letter-spacing:-0.025em;">{{ __('Dashboard') }}</h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ now()->translatedFormat('l, j F Y') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('invoices.create') }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all"
                style="background:#6366f1;" onmouseover="this.style.background='#4f46e5'"
                onmouseout="this.style.background='#6366f1'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('New Invoice') }}
            </a>
            <a href="{{ route('timer') }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                style="background:white;color:#374151;border:1px solid #e5e7eb;"
                onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('Start Timer') }}
            </a>
        </div>
    </div>

    {{-- ─── Primary stats ───────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Revenue this month --}}
        <div class="bg-white rounded-2xl p-6" style="border:1px solid #eaecf0;">
            <div class="flex items-start justify-between mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                    style="background:rgba(34,197,94,0.1);">
                    <svg style="width:18px;height:18px;color:#22c55e;" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <a href="{{ route('invoices.index', ['statusFilter' => 'paid']) }}" wire:navigate
                    class="text-xs font-medium text-gray-400 hover:text-indigo-600 transition-colors">{{ __('View') }}
                    →</a>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider mb-1.5"
                style="color:#9ca3af;letter-spacing:0.07em;">{{ __('Revenue This Month') }}</p>
            <p class="font-bold leading-none text-gray-900 truncate"
                style="font-family:'Syne',sans-serif;font-size:2rem;letter-spacing:-0.03em;">
                {{ $defaultCurrency }} {{ number_format($revenueThisMonth, 0) }}
            </p>
        </div>

        {{-- Unpaid invoices --}}
        <div class="bg-white rounded-2xl p-6" style="border:1px solid #eaecf0;">
            <div class="flex items-start justify-between mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                    style="background:rgba(245,158,11,0.1);">
                    <svg style="width:18px;height:18px;color:#f59e0b;" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <a href="{{ route('invoices.index', ['statusFilter' => 'sent']) }}" wire:navigate
                    class="text-xs font-medium text-gray-400 hover:text-indigo-600 transition-colors">{{ __('View') }}
                    →</a>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider mb-1.5"
                style="color:#9ca3af;letter-spacing:0.07em;">{{ __('Unpaid Invoices') }}</p>
            <p class="font-bold leading-none text-gray-900"
                style="font-family:'Syne',sans-serif;font-size:2rem;letter-spacing:-0.03em;">{{ $unpaidInvoicesCount }}
            </p>
            <p class="mt-2 text-sm text-gray-500">{{ $defaultCurrency }} {{ number_format($unpaidInvoicesTotal, 0) }}
                {{ __('outstanding') }}</p>
        </div>

        {{-- Overdue --}}
        <div class="rounded-2xl p-6"
            style="border:1px solid {{ $overdueInvoicesCount > 0 ? '#fecaca' : '#eaecf0' }};background:{{ $overdueInvoicesCount > 0 ? '#fff8f8' : 'white' }};">
            <div class="flex items-start justify-between mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                    style="background:{{ $overdueInvoicesCount > 0 ? 'rgba(239,68,68,0.1)' : 'rgba(34,197,94,0.1)' }};">
                    @if ($overdueInvoicesCount > 0)
                        <svg style="width:18px;height:18px;color:#ef4444;" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    @else
                        <svg style="width:18px;height:18px;color:#22c55e;" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                </div>
                @if ($overdueInvoicesCount > 0)
                    <a href="{{ route('invoices.index', ['statusFilter' => 'overdue']) }}" wire:navigate
                        class="text-xs font-medium text-red-400 hover:text-red-600 transition-colors">{{ __('View') }}
                        →</a>
                @endif
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider mb-1.5"
                style="color:{{ $overdueInvoicesCount > 0 ? '#f87171' : '#9ca3af' }};letter-spacing:0.07em;">
                {{ __('Overdue') }}</p>
            <p class="font-bold leading-none"
                style="font-family:'Syne',sans-serif;font-size:2rem;letter-spacing:-0.03em;color:{{ $overdueInvoicesCount > 0 ? '#dc2626' : '#111827' }};">
                {{ $overdueInvoicesCount }}</p>
            @if ($overdueInvoicesCount > 0)
                <p class="mt-2 text-sm font-medium" style="color:#ef4444;">{{ __('Requires attention') }}</p>
            @else
                <p class="mt-2 text-sm font-medium" style="color:#22c55e;">{{ __('All caught up!') }}</p>
            @endif
        </div>

    </div>

    {{-- ─── Secondary stats ─────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Tracked hours --}}
        <div class="bg-white rounded-2xl px-5 py-4 flex items-center gap-4" style="border:1px solid #eaecf0;">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                style="background:rgba(99,102,241,0.1);">
                <svg style="width:16px;height:16px;color:#6366f1;" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-semibold uppercase tracking-wider truncate"
                    style="color:#9ca3af;letter-spacing:0.07em;">{{ __('Hours This Month') }}</p>
                <p class="text-xl font-bold text-gray-900 leading-tight" style="font-family:'Syne',sans-serif;">
                    {{ $trackedHoursThisMonth }}<span class="text-base font-semibold text-gray-400 ml-0.5">h</span>
                </p>
            </div>
        </div>

        {{-- Active projects --}}
        <a href="{{ route('projects.index') }}" wire:navigate
            class="bg-white rounded-2xl px-5 py-4 flex items-center gap-4 group transition-all hover:shadow-sm"
            style="border:1px solid #eaecf0;">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                style="background:rgba(139,92,246,0.1);">
                <svg style="width:16px;height:16px;color:#8b5cf6;" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-semibold uppercase tracking-wider truncate"
                    style="color:#9ca3af;letter-spacing:0.07em;">{{ __('Active Projects') }}</p>
                <p class="text-xl font-bold text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors"
                    style="font-family:'Syne',sans-serif;">{{ $activeProjects }}</p>
            </div>
        </a>

        {{-- Total clients --}}
        <a href="{{ route('clients.index') }}" wire:navigate
            class="bg-white rounded-2xl px-5 py-4 flex items-center gap-4 group transition-all hover:shadow-sm"
            style="border:1px solid #eaecf0;">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                style="background:rgba(14,165,233,0.1);">
                <svg style="width:16px;height:16px;color:#0ea5e9;" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-semibold uppercase tracking-wider truncate"
                    style="color:#9ca3af;letter-spacing:0.07em;">{{ __('Total Clients') }}</p>
                <p class="text-xl font-bold text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors"
                    style="font-family:'Syne',sans-serif;">{{ $totalClients }}</p>
            </div>
        </a>

        {{-- Expenses this month --}}
        <a href="{{ route('expenses.index') }}" wire:navigate
            class="bg-white rounded-2xl px-5 py-4 flex items-center gap-4 group transition-all hover:shadow-sm"
            style="border:1px solid #eaecf0;">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                style="background:rgba(239,68,68,0.08);">
                <svg style="width:16px;height:16px;color:#ef4444;" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path
                        d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-semibold uppercase tracking-wider truncate"
                    style="color:#9ca3af;letter-spacing:0.07em;">{{ __('Expenses This Month') }}</p>
                <p class="text-xl font-bold text-gray-900 leading-tight group-hover:text-indigo-600 transition-colors truncate"
                    style="font-family:'Syne',sans-serif;">{{ $defaultCurrency }}
                    {{ number_format($expensesThisMonth, 0) }}</p>
            </div>
        </a>

    </div>

    {{-- ─── Invoice usage meter (limited plans only) ───────────── --}}
    @if ($invoicesMonthlyLimit !== null)
        @php
            $usagePct =
                $invoicesMonthlyLimit > 0 ? min(100, round(($invoicesThisMonth / $invoicesMonthlyLimit) * 100)) : 100;
            $usageColor = $usagePct >= 100 ? '#ef4444' : ($usagePct >= 80 ? '#f59e0b' : '#6366f1');
            $usageBg =
                $usagePct >= 100
                    ? 'rgba(239,68,68,0.08)'
                    : ($usagePct >= 80
                        ? 'rgba(245,158,11,0.08)'
                        : 'rgba(99,102,241,0.08)');
        @endphp
        <div class="bg-white rounded-2xl px-6 py-4" style="border:1px solid #eaecf0;">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                        style="background:{{ $usageBg }};">
                        <svg style="width:14px;height:14px;color:{{ $usageColor }};" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            viewBox="0 0 24 24">
                            <path
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-600">
                        {{ __('Invoices This Month') }}:
                        <span class="font-bold" style="color:{{ $usageColor }};">{{ $invoicesThisMonth }}</span>
                        <span class="text-gray-400">/ {{ $invoicesMonthlyLimit }}</span>
                    </span>
                </div>
                @if ($usagePct >= 80)
                    <a href="{{ route('billing.index') }}" wire:navigate
                        class="text-xs font-semibold transition-colors"
                        style="color:{{ $usageColor }};">{{ __('Upgrade') }} →</a>
                @endif
            </div>
            <div class="w-full rounded-full h-1.5" style="background:#f3f4f6;">
                <div class="h-1.5 rounded-full transition-all duration-300"
                    style="width:{{ $usagePct }}%;background:{{ $usageColor }};"></div>
            </div>
            @if ($usagePct >= 100)
                <p class="mt-1.5 text-[11px] font-medium" style="color:#ef4444;">
                    {{ __('Monthly limit reached — upgrade to send more invoices.') }}</p>
            @elseif ($usagePct >= 80)
                <p class="mt-1.5 text-[11px] font-medium" style="color:#f59e0b;">
                    {{ __('Approaching your monthly limit.') }}</p>
            @endif
        </div>
    @endif

    {{-- ─── Quick Actions ───────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl p-5" style="border:1px solid #eaecf0;">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4" style="letter-spacing:0.09em;">
            {{ __('Quick Actions') }}</h3>
        <div class="grid grid-cols-4 sm:grid-cols-8 gap-3">

            @php
                $quickActions = [
                    [
                        'label' => __('New Invoice'),
                        'route' => 'invoices.create',
                        'icon' =>
                            'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        'color' => '#6366f1',
                        'bg' => 'rgba(99,102,241,0.08)',
                    ],
                    [
                        'label' => __('Add Client'),
                        'route' => 'clients.create',
                        'icon' =>
                            'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                        'color' => '#0ea5e9',
                        'bg' => 'rgba(14,165,233,0.08)',
                    ],
                    [
                        'label' => __('New Project'),
                        'route' => 'projects.create',
                        'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
                        'color' => '#8b5cf6',
                        'bg' => 'rgba(139,92,246,0.08)',
                    ],
                    [
                        'label' => __('Log Time'),
                        'route' => 'timer',
                        'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        'color' => '#f59e0b',
                        'bg' => 'rgba(245,158,11,0.08)',
                    ],
                    [
                        'label' => __('Add Expense'),
                        'route' => 'expenses.create',
                        'icon' =>
                            'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
                        'color' => '#ef4444',
                        'bg' => 'rgba(239,68,68,0.08)',
                    ],
                    [
                        'label' => __('Settings'),
                        'route' => 'settings.index',
                        'icon' =>
                            'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                        'color' => '#6b7280',
                        'bg' => 'rgba(107,114,128,0.08)',
                    ],
                    [
                        'label' => __('Import Invoices'),
                        'route' => 'invoices.import',
                        'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347A3.75 3.75 0 0113.5 21h-3a3.75 3.75 0 01-2.652-1.098l-.347-.347z',
                        'color' => '#0d9488',
                        'bg' => 'rgba(13,148,136,0.08)',
                    ],
                    [
                        'label' => __('Import Expenses'),
                        'route' => 'expenses.import',
                        'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347A3.75 3.75 0 0113.5 21h-3a3.75 3.75 0 01-2.652-1.098l-.347-.347z',
                        'color' => '#f97316',
                        'bg' => 'rgba(249,115,22,0.08)',
                    ],
                ];
            @endphp

            @foreach ($quickActions as $action)
                <a href="{{ route($action['route']) }}" wire:navigate
                    class="flex flex-col items-center gap-2 p-3 rounded-xl transition-all group"
                    style="background:{{ $action['bg'] }};" onmouseover="this.style.opacity='0.8'"
                    onmouseout="this.style.opacity='1'">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                        style="background:{{ $action['color'] }}15;">
                        <svg style="width:17px;height:17px;color:{{ $action['color'] }};" fill="none"
                            stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                            viewBox="0 0 24 24">
                            <path d="{{ $action['icon'] }}" />
                        </svg>
                    </div>
                    <span class="text-[11px] font-semibold text-center leading-tight"
                        style="color:#374151;">{{ $action['label'] }}</span>
                </a>
            @endforeach

        </div>
    </div>

    {{-- ─── Recent Invoices + Upcoming Due ─────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Recent Invoices (2/3) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl overflow-hidden" style="border:1px solid #eaecf0;">
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid #f3f4f6;">
                <h3 class="text-sm font-bold text-gray-900">{{ __('Recent Invoices') }}</h3>
                <a href="{{ route('invoices.index') }}" wire:navigate
                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">{{ __('View all') }}
                    →</a>
            </div>

            @if ($recentInvoices->isEmpty())
                <div class="px-6 py-12 text-center">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm text-gray-400 mb-3">{{ __('No invoices yet') }}</p>
                    <a href="{{ route('invoices.create') }}" wire:navigate
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">{{ __('Create your first invoice') }}
                        →</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr style="background:#fafafa;">
                                <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400"
                                    style="letter-spacing:0.07em;">{{ __('Invoice') }}</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400"
                                    style="letter-spacing:0.07em;">{{ __('Client') }}</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-400"
                                    style="letter-spacing:0.07em;">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-gray-400"
                                    style="letter-spacing:0.07em;">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentInvoices as $inv)
                                @php
                                    $statusStyles = match ($inv->status) {
                                        'paid' => [
                                            'bg' => 'rgba(34,197,94,0.1)',
                                            'color' => '#16a34a',
                                            'label' => __('Paid'),
                                        ],
                                        'sent' => [
                                            'bg' => 'rgba(99,102,241,0.1)',
                                            'color' => '#4f46e5',
                                            'label' => __('Sent'),
                                        ],
                                        'overdue' => [
                                            'bg' => 'rgba(239,68,68,0.1)',
                                            'color' => '#dc2626',
                                            'label' => __('Overdue'),
                                        ],
                                        default => [
                                            'bg' => 'rgba(156,163,175,0.15)',
                                            'color' => '#6b7280',
                                            'label' => __('Draft'),
                                        ],
                                    };
                                @endphp
                                <tr class="transition-colors" style="border-top:1px solid #f3f4f6;"
                                    onmouseover="this.style.background='#fafafa'"
                                    onmouseout="this.style.background=''">
                                    <td class="px-6 py-3.5 text-sm font-semibold">
                                        <a href="{{ route('invoices.show', $inv) }}" wire:navigate
                                            class="text-indigo-600 hover:text-indigo-800 transition-colors">{{ $inv->invoice_number }}</a>
                                        <span
                                            class="block text-[11px] text-gray-400 mt-0.5">{{ $inv->issue_date->format('d M Y') }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 text-sm text-gray-700">{{ $inv->client?->name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                            style="background:{{ $statusStyles['bg'] }};color:{{ $statusStyles['color'] }};">
                                            {{ $statusStyles['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-sm font-bold text-right text-gray-900">
                                        {{ $inv->currency }} {{ number_format($inv->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Upcoming Due (1/3) --}}
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #eaecf0;">
            <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #f3f4f6;">
                <h3 class="text-sm font-bold text-gray-900">{{ __('Due Soon') }}</h3>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                    style="background:rgba(245,158,11,0.1);color:#d97706;">{{ __('Next 7 days') }}</span>
            </div>

            @if ($upcomingInvoices->isEmpty())
                <div class="px-5 py-10 text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-gray-400">{{ __('Nothing due soon') }}</p>
                </div>
            @else
                <ul class="divide-y divide-gray-50">
                    @foreach ($upcomingInvoices as $inv)
                        @php $daysLeft = (int) now()->diffInDays($inv->due_date, false); @endphp
                        <li class="px-5 py-3.5 transition-colors" onmouseover="this.style.background='#fafafa'"
                            onmouseout="this.style.background=''">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('invoices.show', $inv) }}" wire:navigate
                                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors block truncate">{{ $inv->invoice_number }}</a>
                                    <p class="text-xs text-gray-500 truncate">{{ $inv->client?->name ?? '—' }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-gray-900">{{ $inv->currency }}
                                        {{ number_format($inv->total, 0) }}</p>
                                    <p class="text-[11px] font-semibold mt-0.5"
                                        style="color:{{ $daysLeft <= 2 ? '#ef4444' : '#f59e0b' }};">
                                        {{ $daysLeft === 0 ? __('Today') : ($daysLeft === 1 ? __('Tomorrow') : __(':n days', ['n' => $daysLeft])) }}
                                    </p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- Useful links --}}
            <div class="px-5 py-4 space-y-1" style="border-top:1px solid #f3f4f6;background:#fafafa;">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2"
                    style="letter-spacing:0.09em;">{{ __('Shortcuts') }}</p>
                @php
                    $shortcuts = [
                        [
                            'label' => __('All Invoices'),
                            'route' => 'invoices.index',
                            'icon' =>
                                'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        ],
                        [
                            'label' => __('Clients'),
                            'route' => 'clients.index',
                            'icon' =>
                                'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                        ],
                        [
                            'label' => __('Projects'),
                            'route' => 'projects.index',
                            'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
                        ],
                        [
                            'label' => __('Expenses'),
                            'route' => 'expenses.index',
                            'icon' =>
                                'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
                        ],
                        [
                            'label' => __('Settings'),
                            'route' => 'settings.index',
                            'icon' =>
                                'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                        ],
                        [
                            'label' => __('Billing'),
                            'route' => 'billing.index',
                            'icon' =>
                                'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                        ],
                    ];
                @endphp
                @foreach ($shortcuts as $sc)
                    <a href="{{ route($sc['route']) }}" wire:navigate
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium text-gray-600 transition-all"
                        onmouseover="this.style.background='white';this.style.color='#4f46e5'"
                        onmouseout="this.style.background='';this.style.color='#4b5563'">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="{{ $sc['icon'] }}" />
                        </svg>
                        {{ $sc['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ─── Overdue invoices table ──────────────────────────────── --}}
    @if ($overdueInvoices->isNotEmpty())
        <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #fecaca;">
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid #fef2f2;">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse inline-block"></span>
                    <h3 class="text-sm font-bold text-red-600">{{ __('Overdue Invoices') }}</h3>
                </div>
                <a href="{{ route('invoices.index', ['statusFilter' => 'overdue']) }}" wire:navigate
                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                    {{ __('View all') }} →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr style="background:#fef2f2;">
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider"
                                style="color:#f87171;letter-spacing:0.07em;">{{ __('Invoice #') }}</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider"
                                style="color:#f87171;letter-spacing:0.07em;">{{ __('Client') }}</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider"
                                style="color:#f87171;letter-spacing:0.07em;">{{ __('Due Date') }}</th>
                            <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider"
                                style="color:#f87171;letter-spacing:0.07em;">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($overdueInvoices as $inv)
                            <tr class="transition-colors" style="border-top:1px solid #fef2f2;"
                                onmouseover="this.style.background='#fff8f8'" onmouseout="this.style.background=''">
                                <td class="px-6 py-3.5 text-sm font-semibold">
                                    <a href="{{ route('invoices.show', $inv) }}" wire:navigate
                                        class="text-indigo-600 hover:text-indigo-800 transition-colors">{{ $inv->invoice_number }}</a>
                                </td>
                                <td class="px-6 py-3.5 text-sm text-gray-700">{{ $inv->client?->name ?? '—' }}</td>
                                <td class="px-6 py-3.5">
                                    <span
                                        class="text-sm font-medium text-red-600">{{ $inv->due_date->format('d M Y') }}</span>
                                    <span class="ml-2 text-xs"
                                        style="color:#f87171;">{{ $inv->due_date->diffInDays(now()) }}d
                                        {{ __('ago') }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-sm font-bold text-right text-gray-900">
                                    {{ $inv->currency }} {{ number_format($inv->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
