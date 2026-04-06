<div class="p-6 lg:p-8">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-lg font-bold shrink-0"
                style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                {{ strtoupper(substr($client->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-[26px] font-bold text-gray-900 leading-tight" style="font-family:'Syne',sans-serif;letter-spacing:-0.025em;">{{ $client->name }}</h1>
                <p class="mt-0.5 text-sm text-gray-500">{{ __('Client Details') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('clients.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('All Clients') }}
            </a>
            <a href="{{ route('clients.edit', $client) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all"
                style="background:#0f1117;color:white;"
                onmouseover="this.style.background='#1e2130'" onmouseout="this.style.background='#0f1117'">
                {{ __('Edit') }}
            </a>
        </div>
    </div>

    {{-- Client info + stats --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-6">

        {{-- Stats --}}
        @foreach ([
            ['label' => __('Total Invoiced'), 'value' => formatCurrency($client->currency ?: 'EUR', $this->totalInvoiced), 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => '#6366f1'],
            ['label' => __('Total Paid'), 'value' => formatCurrency($client->currency ?: 'EUR', $this->totalPaid), 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#22c55e'],
            ['label' => __('Outstanding'), 'value' => formatCurrency($client->currency ?: 'EUR', $this->totalOutstanding), 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#f97316'],
            ['label' => __('Total Expenses'), 'value' => formatCurrency('EUR', $this->totalExpenses), 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'color' => '#8b5cf6'],
        ] as $stat)
            <div class="bg-white rounded-2xl px-5 py-4" style="border:1px solid #eaecf0;">
                <div class="flex items-center gap-3 mb-1.5">
                    <div class="w-7 h-7 rounded-xl flex items-center justify-center" style="background:{{ $stat['color'] }}1a;">
                        <svg class="w-4 h-4" style="color:{{ $stat['color'] }};" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-400">{{ $stat['label'] }}</span>
                </div>
                <p class="text-xl font-bold text-gray-900">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Client info card --}}
        <div>
            <div class="bg-white rounded-2xl p-5" style="border:1px solid #eaecf0;">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">{{ __('Contact') }}</h2>
                <dl class="space-y-3">
                    @if($client->email)
                        <div>
                            <dt class="text-xs text-gray-400">{{ __('Email') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $client->email }}</dd>
                        </div>
                    @endif
                    @if($client->address)
                        <div>
                            <dt class="text-xs text-gray-400">{{ __('Address') }}</dt>
                            <dd class="text-sm text-gray-700 whitespace-pre-line">{{ $client->address }}</dd>
                        </div>
                    @endif
                    @if($client->country)
                        <div>
                            <dt class="text-xs text-gray-400">{{ __('Country') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $client->country }}</dd>
                        </div>
                    @endif
                    @if($client->vat_number)
                        <div>
                            <dt class="text-xs text-gray-400">{{ __('VAT Number') }}</dt>
                            <dd><span class="text-xs font-mono font-semibold text-gray-800 px-2 py-1 rounded-lg" style="background:#f3f4f6;">{{ $client->vat_number }}</span></dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400">{{ __('Currency') }}</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $client->currency ?: '—' }}</dd>
                    </div>
                </dl>

                {{-- Shortcuts --}}
                <div class="mt-5 pt-4 space-y-2" style="border-top:1px solid #f3f4f6;">
                    <a href="{{ route('invoices.create') }}?client={{ $client->id }}"
                        class="flex items-center gap-2 w-full px-3 py-2 rounded-xl text-sm font-semibold text-indigo-700 hover:bg-indigo-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('New Invoice for Client') }}
                    </a>
                    <a href="{{ route('expenses.create') }}?client={{ $client->id }}"
                        class="flex items-center gap-2 w-full px-3 py-2 rounded-xl text-sm font-semibold text-purple-700 hover:bg-purple-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('New Expense for Client') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Tables column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Recent invoices --}}
            <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #eaecf0;">
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #f3f4f6;">
                    <h2 class="text-sm font-bold text-gray-700">{{ __('Recent Invoices') }}</h2>
                    <a href="{{ route('invoices.index', ['clientFilter' => $client->id]) }}"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-900 transition-colors">
                        {{ __('View All Invoices') }} →
                    </a>
                </div>

                @if ($this->recentInvoices->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-gray-400">{{ __('No invoices yet for this client') }}</p>
                    </div>
                @else
                    <table class="min-w-full">
                        <thead style="background:#fafafa;">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Invoice #') }}</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Date') }}</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->recentInvoices as $invoice)
                                @php
                                    $badgeStyle = match($invoice->status) {
                                        'paid'      => 'background:#f0fdf4;color:#15803d;',
                                        'sent'      => 'background:#eff6ff;color:#1d4ed8;',
                                        'overdue'   => 'background:#fef2f2;color:#dc2626;',
                                        'cancelled' => 'background:#fef2f2;color:#7f1d1d;',
                                        default     => 'background:#f9fafb;color:#6b7280;',
                                    };
                                @endphp
                                <tr style="border-top:1px solid #f3f4f6;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                                    <td class="px-5 py-3.5 text-sm">
                                        <a href="{{ route('invoices.edit', $invoice) }}" class="font-semibold text-gray-900 hover:text-indigo-700">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $invoice->issue_date->format('d M Y') }}</td>
                                    <td class="px-5 py-3.5">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold" style="{{ $badgeStyle }}">{{ __(ucfirst($invoice->status)) }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-900 text-right">{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Recent expenses --}}
            <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #eaecf0;">
                <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #f3f4f6;">
                    <h2 class="text-sm font-bold text-gray-700">{{ __('Recent Expenses') }}</h2>
                    <a href="{{ route('expenses.index', ['clientFilter' => $client->id]) }}"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-900 transition-colors">
                        {{ __('View All Expenses') }} →
                    </a>
                </div>

                @if ($this->recentExpenses->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-gray-400">{{ __('No expenses yet for this client') }}</p>
                    </div>
                @else
                    <table class="min-w-full">
                        <thead style="background:#fafafa;">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Description') }}</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Date') }}</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Category') }}</th>
                                <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->recentExpenses as $expense)
                                <tr style="border-top:1px solid #f3f4f6;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                                    <td class="px-5 py-3.5 text-sm">
                                        <a href="{{ route('expenses.edit', $expense) }}" class="font-medium text-gray-900 hover:text-indigo-700 truncate block max-w-[180px]">{{ $expense->description }}</a>
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $expense->date->format('d M Y') }}</td>
                                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ ucfirst($expense->category) }}</td>
                                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-900 text-right">{{ $expense->currency }} {{ number_format((float) $expense->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
