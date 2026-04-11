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
            font-size: 9pt;
            color: #111827;
            background: #fff;
            line-height: 1.45;
        }

        .page {
            padding: 28px 36px;
        }

        /* Header */
        .header {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 12px;
        }

        .brand {
            font-size: 16pt;
            font-weight: bold;
            color: #1e40af;
        }

        .invoice-number-block {
            text-align: right;
        }

        .invoice-number-block .inv-num {
            font-size: 11pt;
            font-weight: bold;
            color: #111827;
        }

        .invoice-number-block .inv-label {
            font-size: 7.5pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .paid-chip {
            display: inline-block;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 3px;
            padding: 1px 7px;
            font-size: 7.5pt;
            font-weight: bold;
            margin-top: 3px;
        }

        .cancelled-chip {
            display: inline-block;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 3px;
            padding: 1px 7px;
            font-size: 7.5pt;
            font-weight: bold;
            margin-top: 3px;
        }

        /* Parties + Dates combined row */
        .meta-row {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
        }

        .meta-cell {
            vertical-align: top;
        }

        .party-label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .party-name {
            font-size: 9.5pt;
            font-weight: bold;
            color: #111827;
        }

        .party-detail {
            font-size: 8.5pt;
            color: #4b5563;
            line-height: 1.5;
        }

        .vat-badge {
            display: inline-block;
            background: #f3f4f6;
            color: #4b5563;
            border-radius: 2px;
            padding: 0 5px;
            font-size: 7.5pt;
            font-weight: bold;
        }

        .date-item {
            margin-bottom: 7px;
        }

        .date-label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            font-weight: bold;
        }

        .date-value {
            font-size: 9pt;
            font-weight: bold;
            color: #111827;
        }

        /* Items — dense */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.items thead tr {
            background: #1e40af;
            color: #fff;
        }

        table.items thead th {
            padding: 5px 8px;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-align: left;
            font-weight: bold;
        }

        table.items thead th.right {
            text-align: right;
        }

        table.items tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        table.items tbody td {
            padding: 5px 8px;
            font-size: 8.5pt;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        table.items tbody td.right {
            text-align: right;
        }

        /* Totals */
        .totals {
            width: 220px;
            margin-left: auto;
            margin-bottom: 14px;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals table td {
            padding: 3px 0;
            font-size: 8.5pt;
            color: #4b5563;
        }

        .totals table td.right {
            text-align: right;
        }

        .totals .grand-total td {
            font-size: 10pt;
            font-weight: bold;
            color: #1e40af;
            border-top: 2px solid #1e40af;
            padding-top: 5px;
        }

        /* VAT notice */
        .vat-notice {
            margin-bottom: 12px;
            padding: 7px 10px;
            border-radius: 3px;
            font-size: 8pt;
            line-height: 1.4;
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
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            font-size: 8pt;
            color: #4b5563;
            margin-bottom: 14px;
        }

        .notes-label {
            font-weight: bold;
            color: #374151;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 3px;
        }

        /* Footer */
        .footer {
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 7pt;
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
                <td style="vertical-align:middle;">
                    @if ($company?->logoUrl())
                        <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}"
                            style="max-height:36px; max-width:140px;">
                    @else
                        <div class="brand">{{ $company?->name ?? $invoice->user->name }}</div>
                    @endif
                </td>
                <td style="vertical-align:middle; text-align:right;">
                    <div class="invoice-number-block">
                        @php
                            $docLabel = match ($invoice->document_type ?? 'invoice') {
                                'credit_note' => __('Credit Note'),
                                'debit_note' => __('Debit Note'),
                                'proforma' => __('Proforma Invoice'),
                                default => __('Invoice'),
                            };
                        @endphp
                        <div class="inv-label">{{ $docLabel }}</div>
                        <div class="inv-num">{{ $invoice->invoice_number }}</div>
                        @if ($invoice->status === 'paid')
                            <span class="paid-chip">&#10003; {{ __('PAID') }}</span>
                        @elseif ($invoice->status === 'cancelled')
                            <span class="cancelled-chip">&#10007; {{ __('CANCELLED') }}</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Parties + Dates --}}
        <table class="meta-row" cellpadding="0" cellspacing="0">
            <tr>
                {{-- FROM --}}
                <td class="meta-cell" style="width:35%; padding-right:12px;">
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
                            @include('invoices.partials.payment-method-pdf')
                        @else
                            {{ $invoice->user->email }}
                        @endif
                    </div>
                </td>
                {{-- BILL TO --}}
                <td class="meta-cell" style="width:35%; padding-right:12px;">
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
                {{-- DATES --}}
                <td class="meta-cell" style="width:30%; text-align:right;">
                    <div class="date-item">
                        <div class="date-label">{{ __('Issue Date') }}</div>
                        <div class="date-value">{{ $invoice->issue_date->format('d M Y') }}</div>
                    </div>
                    <div class="date-item">
                        <div class="date-label">{{ __('Due Date') }}</div>
                        <div class="date-value">{{ $invoice->due_date->format('d M Y') }}</div>
                    </div>
                    @if ($invoice->paid_at)
                        <div class="date-item">
                            <div class="date-label">{{ __('Payment Date') }}</div>
                            <div class="date-value">{{ $invoice->paid_at->format('d M Y') }}</div>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- Items --}}
        <table class="items">
            <thead>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <th class="right" style="width:55px">{{ __('Qty') }}</th>
                    <th class="right" style="width:90px">{{ __('Unit Price') }}</th>
                    <th class="right" style="width:100px">{{ __('Amount') }}</th>
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
        @if ($invoice->vat_exempt_applied && $invoice->vat_legal_basis)
            <div class="vat-notice exempt">{!! nl2br(e($invoice->vat_legal_basis)) !!}</div>
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
