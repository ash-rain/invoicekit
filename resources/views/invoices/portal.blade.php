<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Invoice') }} {{ $invoice->invoice_number }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=syne:400,500,600,700|dm-sans:400,500,600&display=swap"
        rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-[#f5f6fa]" style="font-family:'DM Sans',sans-serif;">

    {{-- Top bar --}}
    <header class="bg-white border-b border-[#eaecf0] py-4">
        <div class="max-w-3xl mx-auto px-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($company?->logoUrl())
                    <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}" class="h-8 w-auto object-contain">
                @else
                    <span class="text-lg font-bold tracking-tight text-[#0f1117]"
                        style="font-family:'Syne',sans-serif;">
                        {{ $company?->name ?? $invoice->user->name }}
                    </span>
                @endif
            </div>
            <a href="{{ route('invoice.portal', $accessToken->token) }}?download=1"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-[#0f1117] text-white rounded-xl hover:bg-[#1a1f2e] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                {{ __('Download PDF') }}
            </a>
            @if ($invoice->stripe_payment_link_url && $invoice->status !== 'paid')
                <a href="{{ $invoice->stripe_payment_link_url }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    {{ __('Pay Online') }}
                </a>
            @endif
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-10">

        {{-- Status badge --}}
        @php
            $badgeColor = match ($invoice->status) {
                'paid' => 'bg-green-100 text-green-800',
                'sent' => 'bg-blue-100 text-blue-800',
                'overdue' => 'bg-red-100 text-red-800',
                default => 'bg-gray-100 text-gray-700',
            };
        @endphp
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-[#0f1117]" style="font-family:'Syne',sans-serif;">
                {{ __('Invoice') }} {{ $invoice->invoice_number }}
            </h1>
            <span class="inline-flex items-center px-3 py-1 text-sm font-bold rounded-full {{ $badgeColor }}">
                {{ ucfirst($invoice->status) }}
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-[#eaecf0] p-6 space-y-6">

            {{-- Parties --}}
            <div class="grid grid-cols-2 gap-6 pb-6 border-b border-[#f3f4f6]">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('From') }}
                    </p>
                    <p class="text-sm font-semibold text-gray-900">{{ $company?->name ?? $invoice->user->name }}</p>
                    @if ($company?->vat_number)
                        <p class="text-xs text-gray-500 font-mono">{{ __('VAT:') }} {{ $company->vat_number }}</p>
                    @endif
                    @if ($company?->address_line1)
                        <p class="text-xs text-gray-500">{{ $company->address_line1 }}</p>
                    @endif
                    @php $portalPm = $invoice->resolvedPaymentMethod(); @endphp
                    @if ($portalPm && $portalPm['type'] === 'bank_transfer' && ($portalPm['bank_iban'] ?? null))
                        <p class="text-xs text-gray-500 font-mono mt-1">{{ __('IBAN:') }} {{ $portalPm['bank_iban'] }}</p>
                        @if ($portalPm['bank_bic'] ?? null)
                            <p class="text-xs text-gray-500 font-mono">{{ __('BIC:') }} {{ $portalPm['bank_bic'] }}</p>
                        @endif
                    @elseif ($company?->bank_iban)
                        <p class="text-xs text-gray-500 font-mono mt-1">{{ __('IBAN:') }} {{ $company->bank_iban }}</p>
                        @if ($company?->bank_bic)
                            <p class="text-xs text-gray-500 font-mono">{{ __('BIC:') }} {{ $company->bank_bic }}</p>
                        @endif
                    @endif
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Bill To') }}
                    </p>
                    <p class="text-sm font-semibold text-gray-900">{{ $invoice->client->name }}</p>
                    @if ($invoice->client->address)
                        <p class="text-xs text-gray-500 whitespace-pre-line">{{ $invoice->client->address }}</p>
                    @endif
                    @if ($invoice->client->vat_number)
                        <p class="text-xs text-gray-500 font-mono">{{ __('VAT:') }}
                            {{ $invoice->client->vat_number }}</p>
                    @endif
                </div>
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-3 gap-4 pb-6 border-b border-[#f3f4f6]">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Issue Date') }}
                    </p>
                    <p class="text-sm text-gray-900">{{ $invoice->issue_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">{{ __('Due Date') }}
                    </p>
                    <p class="text-sm {{ $invoice->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                    </p>
                </div>
                @if ($invoice->paid_at)
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                            {{ __('Paid On') }}</p>
                        <p class="text-sm text-green-600 font-semibold">{{ $invoice->paid_at->format('d M Y') }}</p>
                    </div>
                @endif
            </div>

            {{-- Line Items --}}
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-[#fafafa]">
                        <th class="py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            {{ __('Description') }}</th>
                        <th class="py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider w-16">
                            {{ __('Qty') }}</th>
                        <th class="py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider w-28">
                            {{ __('Unit Price') }}</th>
                        <th class="py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider w-28">
                            {{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr class="border-t border-[#f3f4f6]">
                            <td class="py-3 text-gray-800">{{ $item->description }}</td>
                            <td class="py-3 text-right text-gray-600">
                                {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}
                            </td>
                            <td class="py-3 text-right text-gray-600">
                                {{ formatCurrency($invoice->currency, (float) $item->unit_price) }}</td>
                            <td class="py-3 text-right text-gray-800 font-semibold">
                                {{ formatCurrency($invoice->currency, $item->subtotal()) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="flex justify-end border-t border-[#f3f4f6] pt-4">
                <div class="w-64 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __('Subtotal') }}</span>
                        <span>{{ formatCurrency($invoice->currency, (float) $invoice->subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>{{ __('VAT') }} @if ($invoice->vat_rate > 0)
                                ({{ $invoice->vat_rate }}%)
                            @endif
                        </span>
                        <span>{{ formatCurrency($invoice->currency, (float) $invoice->vat_amount) }}</span>
                    </div>
                    @if ($invoice->vat_type && $invoice->vat_type !== 'standard')
                        <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-2.5 py-1.5">
                            @if ($invoice->vat_type === 'reverse_charge')
                                {{ __('VAT Reverse Charge') }}
                            @elseif ($invoice->vat_type === 'oss')
                                {{ __('OSS Scheme') }}
                            @elseif ($invoice->vat_type === 'exempt')
                                {{ __('VAT Exempt') }}
                            @endif
                        </p>
                    @endif
                    <div class="flex justify-between text-[#0f1117] font-bold text-base pt-2 border-t border-[#eaecf0]">
                        <span>{{ __('Total') }}</span>
                        <span>{{ formatCurrency($invoice->currency, (float) $invoice->total) }}</span>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if ($invoice->notes)
                <div class="pt-4 border-t border-[#f3f4f6]">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('Notes') }}
                    </p>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
            @endif

        </div>

        {{-- Payment options (only when invoice is unpaid) --}}
        @if ($invoice->status !== 'paid')
            <div class="mt-6 bg-white rounded-2xl border border-[#eaecf0] p-6">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">{{ __('Payment Options') }}</h2>

                <div class="space-y-3">
                    {{-- Online payment via Stripe --}}
                    @if ($invoice->stripe_payment_link_url)
                        <div class="flex items-center justify-between rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                                    <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ __('Pay by Card') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Visa, Mastercard, and more') }}</p>
                                </div>
                            </div>
                            <a href="{{ $invoice->stripe_payment_link_url }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                                {{ __('Pay Now') }}
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    @endif

                    {{-- Payment method details --}}
                    @php $paymentPm = $invoice->resolvedPaymentMethod(); @endphp
                    @if ($paymentPm && $paymentPm['type'] === 'bank_transfer')
                        <div class="rounded-xl border border-[#eaecf0] bg-[#fafafa] px-4 py-3">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100">
                                    <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                    </svg>
                                </span>
                                <p class="text-sm font-semibold text-gray-900">{{ __('Bank Transfer') }}</p>
                            </div>
                            <div class="space-y-1.5 text-sm pl-11">
                                @if ($paymentPm['bank_name'] ?? null)
                                    <div class="flex gap-2">
                                        <span class="text-gray-500 min-w-[80px]">{{ __('Bank') }}</span>
                                        <span class="text-gray-900">{{ $paymentPm['bank_name'] }}</span>
                                    </div>
                                @endif
                                @if ($paymentPm['bank_iban'] ?? null)
                                    <div class="flex gap-2">
                                        <span class="text-gray-500 min-w-[80px]">IBAN</span>
                                        <span class="font-mono text-gray-900">{{ $paymentPm['bank_iban'] }}</span>
                                    </div>
                                @endif
                                @if ($paymentPm['bank_bic'] ?? null)
                                    <div class="flex gap-2">
                                        <span class="text-gray-500 min-w-[80px]">BIC / SWIFT</span>
                                        <span class="font-mono text-gray-900">{{ $paymentPm['bank_bic'] }}</span>
                                    </div>
                                @endif
                                <div class="flex gap-2">
                                    <span class="text-gray-500 min-w-[80px]">{{ __('Reference') }}</span>
                                    <span class="font-mono text-gray-900">{{ $invoice->invoice_number }}</span>
                                </div>
                            </div>
                        </div>
                    @elseif ($paymentPm && $paymentPm['type'] === 'cash')
                        <div class="rounded-xl border border-[#eaecf0] bg-[#fafafa] px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-50">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ __('Payment in cash') }}</p>
                                    @if ($paymentPm['notes'] ?? null)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $paymentPm['notes'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif ($company?->bank_iban || $company?->bank_bic)
                        {{-- Fallback to legacy company bank fields --}}
                        <div class="rounded-xl border border-[#eaecf0] bg-[#fafafa] px-4 py-3">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100">
                                    <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                    </svg>
                                </span>
                                <p class="text-sm font-semibold text-gray-900">{{ __('Bank Transfer') }}</p>
                            </div>
                            <div class="space-y-1.5 text-sm pl-11">
                                @if ($company->bank_name)
                                    <div class="flex gap-2">
                                        <span class="text-gray-500 min-w-[80px]">{{ __('Bank') }}</span>
                                        <span class="text-gray-900">{{ $company->bank_name }}</span>
                                    </div>
                                @endif
                                @if ($company->bank_iban)
                                    <div class="flex gap-2">
                                        <span class="text-gray-500 min-w-[80px]">IBAN</span>
                                        <span class="font-mono text-gray-900">{{ $company->bank_iban }}</span>
                                    </div>
                                @endif
                                @if ($company->bank_bic)
                                    <div class="flex gap-2">
                                        <span class="text-gray-500 min-w-[80px]">BIC / SWIFT</span>
                                        <span class="font-mono text-gray-900">{{ $company->bank_bic }}</span>
                                    </div>
                                @endif
                                <div class="flex gap-2">
                                    <span class="text-gray-500 min-w-[80px]">{{ __('Reference') }}</span>
                                    <span class="font-mono text-gray-900">{{ $invoice->invoice_number }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Powered by --}}
        <p class="text-center text-xs text-gray-400 mt-8">
            Powered by <a href="{{ url('/') }}"
                class="hover:underline">{{ config('app.name', 'InvoiceKit') }}</a>
        </p>

    </main>

</body>

</html>
