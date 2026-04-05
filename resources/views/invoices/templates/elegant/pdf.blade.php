<!DOCTYPE html>
<html lang="{{ $invoice->language ?? 'en' }}">

<head>
    <meta charset="UTF-8" />
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10.5pt;
            color: #374151;
            background: #fff;
            line-height: 1.6;
        }

        .page {
            padding: 48px 56px;
        }

        /* Header */
        .header {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
        }

        .brand {
            font-size: 19pt;
            font-weight: bold;
            color: #111827;
            letter-spacing: -0.3px;
        }

        .invoice-meta-block {
            text-align: right;
        }

        .invoice-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: #9ca3af;
            font-weight: bold;
        }

        .invoice-number {
            font-size: 16pt;
            font-weight: bold;
            color: #111827;
            margin-top: 3px;
        }

        /* Thin accent rule */
        .accent-rule {
            border: none;
            border-top: 1px solid #d1d5db;
            margin: 0 0 36px 0;
        }

        /* Paid status */
        .paid-tag {
            display: inline-block;
            color: #059669;
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-top: 6px;
        }

        .cancelled-tag {
            display: inline-block;
            color: #dc2626;
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-top: 6px;
        }

        /* Parties */
        .parties {
            width: 100%;
            margin-bottom: 36px;
            border-collapse: collapse;
        }

        .party {
            width: 50%;
            vertical-align: top;
        }

        .party-left {
            padding-right: 28px;
        }

        .party-right {
            padding-left: 28px;
            border-left: 1px solid #e5e7eb;
        }

        .party-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #9ca3af;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .party-name {
            font-size: 12.5pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 6px;
        }

        .party-detail {
            font-size: 9pt;
            color: #6b7280;
            line-height: 1.7;
        }

        .vat-badge {
            display: inline-block;
            background: #f3f4f6;
            color: #6b7280;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 7.5pt;
            font-weight: bold;
            margin-top: 3px;
        }

        /* Dates — clean inline row */
        .dates-row {
            margin-bottom: 36px;
            border-collapse: collapse;
        }

        .date-item {
            padding-right: 32px;
            vertical-align: top;
        }

        .date-label {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9ca3af;
            font-weight: bold;
        }

        .date-value {
            font-size: 10.5pt;
            font-weight: bold;
            color: #111827;
            margin-top: 3px;
        }

        /* Items — refined minimal */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        table.items thead th {
            padding: 7px 10px;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9ca3af;
            font-weight: bold;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        table.items thead th.right {
            text-align: right;
        }

        table.items tbody td {
            padding: 10px 10px;
            font-size: 9.5pt;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
            color: #374151;
        }

        table.items tbody td.right {
            text-align: right;
        }

        /* Totals */
        .totals {
            width: 270px;
            margin-left: auto;
            margin-bottom: 28px;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals table td {
            padding: 6px 0;
            font-size: 9.5pt;
            color: #6b7280;
        }

        .totals table td.right {
            text-align: right;
        }

        .totals .subtotal-rule td {
            border-top: 1px solid #e5e7eb;
        }

        .totals .grand-total td {
            font-size: 12.5pt;
            font-weight: bold;
            color: #111827;
            border-top: 2px solid #d1d5db;
            padding-top: 10px;
            margin-top: 4px;
        }

        /* VAT notice */
        .vat-notice {
            margin-bottom: 20px;
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 9pt;
            line-height: 1.5;
        }

        .vat-notice.reverse-charge {
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            color: #92400e;
        }

        .vat-notice.oss {
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
            color: #1e3a8a;
        }

        .vat-notice.exempt {
            background: #f0fdf4;
            border-left: 3px solid #22c55e;
            color: #14532d;
        }

        /* Notes */
        .notes-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 13px 15px;
            font-size: 9pt;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .notes-label {
            font-weight: bold;
            color: #374151;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 5px;
        }

        /* Footer */
        .footer {
            margin-top: 36px;
            padding-top: 14px;
            border-top: 1px solid #e5e7eb;
            font-size: 7.5pt;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="page">

        {{-- Header --}}
        <table class="header" cellpadding="0" cellspacing="0">
            <tr>
                <td style="vertical-align:top;">
                    @if ($company?->logoUrl())
                        <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}"
                            style="max-height:44px; max-width:160px;">
                    @else
                        <div class="brand">{{ $company?->name ?? $invoice->user->name }}</div>
                    @endif
                </td>
                <td style="vertical-align:top; text-align:right;">
                    <div class="invoice-meta-block">
                        @php
                            $docLabel = match ($invoice->document_type ?? 'invoice') {
                                'credit_note' => __('Credit Note'),
                                'debit_note' => __('Debit Note'),
                                'proforma' => __('Proforma Invoice'),
                                default => __('Invoice'),
                            };
                        @endphp
                        <div class="invoice-label">{{ $docLabel }}</div>
                        <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                        @if ($invoice->status === 'paid')
                            <div class="paid-tag">&#10003; {{ __('Paid') }}</div>
                        @elseif ($invoice->status === 'cancelled')
                            <div class="cancelled-tag">&#10007; {{ __('Cancelled') }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <hr class="accent-rule">

        {{-- Parties --}}
        <table class="parties" cellpadding="0" cellspacing="0">
            <tr>
                <td class="party party-left">
                    <div class="party-label">{{ __('From') }}</div>
                    @php $company = $invoice->user->currentCompany; @endphp
                    <div class="party-name">{{ $company?->name ?? $invoice->user->name }}</div>
                    <div class="party-detail">
                        @if ($company)
                            @if ($company->address_line1)
                                {{ $company->address_line1 }}<br>
                            @endif
                            @if ($company->address_line2)
                                {{ $company->address_line2 }}<br>
                            @endif
                            @if ($company->postal_code || $company->city)
                                {{ implode(' ', array_filter([$company->postal_code, $company->city])) }}<br>
                            @endif
                            @if ($company->country)
                                {{ $company->country }}<br>
                            @endif
                            @if ($company->vat_number)
                                <span class="vat-badge">VAT: {{ $company->vat_number }}</span><br>
                            @endif
                            @if ($company->registration_number)
                                Reg: {{ $company->registration_number }}<br>
                            @endif
                            @if ($company->bank_iban)
                                @if ($company->bank_name)
                                    {{ $company->bank_name }}<br>
                                @endif
                                IBAN: {{ $company->bank_iban }}<br>
                                @if ($company->bank_bic)
                                    BIC: {{ $company->bank_bic }}<br>
                                @endif
                            @endif
                        @else
                            {{ $invoice->user->email }}
                        @endif
                    </div>
                </td>
                <td class="party party-right">
                    <div class="party-label">{{ __('Bill To') }}</div>
                    <div class="party-name">{{ $invoice->client->name }}</div>
                    <div class="party-detail">
                        @if ($invoice->client->address)
                            {!! nl2br(e($invoice->client->address)) !!}<br>
                        @endif
                        {{ $invoice->client->country }}
                        @if ($invoice->client->email)
                            <br>{{ $invoice->client->email }}
                        @endif
                        @if ($invoice->client->vat_number)
                            <br><span class="vat-badge">VAT: {{ $invoice->client->vat_number }}</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Dates --}}
        <table class="dates-row" cellpadding="0" cellspacing="0">
            <tr>
                <td class="date-item">
                    <div class="date-label">{{ __('Issue Date') }}</div>
                    <div class="date-value">{{ $invoice->issue_date->format('d M Y') }}</div>
                </td>
                <td class="date-item">
                    <div class="date-label">{{ __('Due Date') }}</div>
                    <div class="date-value">{{ $invoice->due_date->format('d M Y') }}</div>
                </td>
                @if ($invoice->paid_at)
                    <td class="date-item">
                        <div class="date-label">{{ __('Payment Date') }}</div>
                        <div class="date-value">{{ $invoice->paid_at->format('d M Y') }}</div>
                    </td>
                @endif
            </tr>
        </table>

        {{-- Items --}}
        <table class="items">
            <thead>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <th class="right" style="width:70px">{{ __('Qty') }}</th>
                    <th class="right" style="width:100px">{{ __('Unit Price') }}</th>
                    <th class="right" style="width:110px">{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="right">
                            {{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}</td>
                        <td class="right">{{ formatCurrency($invoice->currency, (float) $item->unit_price) }}</td>
                        <td class="right">{{ formatCurrency($invoice->currency, $item->subtotal()) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- VAT Notice --}}
        @php $vatType = $invoice->vat_type ?? 'standard'; @endphp
        @if ($invoice->vat_exempt_applied && $invoice->vat_exempt_notice)
            <div class="vat-notice exempt">{!! nl2br(e($invoice->vat_exempt_notice)) !!}</div>
        @elseif($vatType === 'reverse_charge')
            <div class="vat-notice reverse-charge">{!! __(
                'VAT Reverse Charge — VAT is to be accounted for by the recipient pursuant to Art. 196 of Council Directive 2006/112/EC.',
            ) !!}</div>
        @elseif($vatType === 'oss')
            <div class="vat-notice oss">{!! __('OSS Scheme — VAT applied at the seller\'s country rate under the EU One-Stop-Shop scheme.') !!}</div>
        @elseif($vatType === 'exempt')
            <div class="vat-notice exempt">{!! __('VAT Exempt — Supply is exempt from VAT (buyer located outside the EU).') !!}</div>
        @endif

        {{-- Totals --}}
        <div class="totals">
            <table>
                <tr>
                    <td>{{ __('Tax Base (Subtotal)') }}</td>
                    <td class="right">{{ formatCurrency($invoice->currency, (float) $invoice->subtotal) }}</td>
                </tr>
                @if (!$invoice->vat_exempt_applied)
                    <tr>
                        <td>{{ __('VAT') }}@if ($invoice->vat_rate > 0)
                                ({{ $invoice->vat_rate }}%)
                            @endif
                        </td>
                        <td class="right">{{ formatCurrency($invoice->currency, (float) $invoice->vat_amount) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td>{{ __('Total Due') }}</td>
                    <td class="right">{{ formatCurrency($invoice->currency, (float) $invoice->total) }}</td>
                </tr>
            </table>
        </div>

        {{-- Notes --}}
        @if ($invoice->notes)
            <div class="notes-section">
                <div class="notes-label">{{ __('Notes') }}</div>
                {!! nl2br(e($invoice->notes)) !!}
            </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            {{ $invoice->invoice_number }} &middot; {{ __('Generated by') }} InvoiceKit &middot;
            {{ now()->format('d M Y') }}
        </div>

    </div>
</body>

</html>
