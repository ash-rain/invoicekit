<!DOCTYPE html>
<html lang="{{ $invoice->language ?? 'en' }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a2e;
            background: #fff;
            line-height: 1.5;
        }

        .page {
            padding: 36px 48px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4f46e5;
        }

        .brand {
            font-size: 22pt;
            font-weight: bold;
            color: #4f46e5;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h2 {
            font-size: 18pt;
            font-weight: bold;
            color: #1a1a2e;
        }

        .invoice-title .number {
            font-size: 12pt;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Parties */
        .parties {
            display: flex;
            justify-content: space-between;
            margin-bottom: 28px;
            gap: 24px;
        }

        .party {
            flex: 1;
        }

        .party-label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .party-name {
            font-size: 13pt;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 3px;
        }

        .party-detail {
            font-size: 9.5pt;
            color: #4b5563;
            line-height: 1.6;
        }

        .vat-badge {
            display: inline-block;
            background: #ede9fe;
            color: #5b21b6;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 8.5pt;
            font-weight: bold;
            margin-top: 4px;
        }

        /* Dates */
        .dates-row {
            display: flex;
            gap: 24px;
            margin-bottom: 28px;
        }

        .date-box {
            background: #f8f9ff;
            border: 1px solid #e0e7ff;
            border-radius: 6px;
            padding: 10px 16px;
        }

        .date-box .label {
            font-size: 8pt;
            text-transform: uppercase;
            color: #9ca3af;
            font-weight: bold;
            letter-spacing: 0.06em;
        }

        .date-box .value {
            font-size: 11pt;
            font-weight: bold;
            color: #1a1a2e;
            margin-top: 2px;
        }

        /* Line items table */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.items thead tr {
            background: #4f46e5;
            color: #fff;
        }

        table.items thead th {
            padding: 8px 10px;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-align: left;
        }

        table.items thead th.right {
            text-align: right;
        }

        table.items tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        table.items tbody td {
            padding: 8px 10px;
            font-size: 10pt;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        table.items tbody td.right {
            text-align: right;
        }

        /* Totals */
        .totals {
            width: 260px;
            margin-left: auto;
            margin-bottom: 28px;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals table td {
            padding: 5px 0;
            font-size: 10pt;
        }

        .totals table td.right {
            text-align: right;
            font-weight: 500;
        }

        .totals .grand-total td {
            font-size: 13pt;
            font-weight: bold;
            color: #4f46e5;
            border-top: 2px solid #4f46e5;
            padding-top: 8px;
        }

        /* VAT notice */
        .vat-notice {
            margin-bottom: 20px;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 9.5pt;
            line-height: 1.5;
        }

        .vat-notice.reverse-charge {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }

        .vat-notice.oss {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            color: #1e3a8a;
        }

        .vat-notice.exempt {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #14532d;
        }

        /* Notes */
        .notes-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 14px;
            font-size: 9.5pt;
            color: #4b5563;
            margin-bottom: 24px;
        }

        .notes-section .notes-label {
            font-weight: bold;
            color: #374151;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 5px;
        }

        /* Footer */
        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 8.5pt;
            color: #9ca3af;
            text-align: center;
        }

        /* Status stamp */
        .status-paid {
            display: inline-block;
            border: 3px solid #16a34a;
            color: #16a34a;
            font-weight: bold;
            font-size: 14pt;
            padding: 4px 14px;
            border-radius: 6px;
            transform: rotate(-8deg);
            opacity: 0.75;
        }

        .status-overlay {
            text-align: right;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="page">

        {{-- Header --}}
        <div class="header">
            @if ($company?->logoUrl())
                <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}"
                    style="max-height:48px; max-width:180px; object-fit:contain;">
            @else
                <div class="brand">{{ $company?->name ?? $invoice->user->name }}</div>
            @endif
            <div class="invoice-title">
                <h2>{{ __('INVOICE') }}</h2>
                <div class="number">{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        {{-- Paid stamp --}}
        @if ($invoice->status === 'paid')
            <div class="status-overlay">
                <span class="status-paid">{{ __('PAID') }}</span>
            </div>
        @endif

        {{-- Parties --}}
        <div class="parties">
            <div class="party">
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
            </div>
            <div class="party">
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
            </div>
        </div>

        {{-- Dates --}}
        <div class="dates-row">
            <div class="date-box">
                <div class="label">{{ __('Issue Date') }}</div>
                <div class="value">{{ $invoice->issue_date->format('d M Y') }}</div>
            </div>
            <div class="date-box">
                <div class="label">{{ __('Due Date') }}</div>
                <div class="value">{{ $invoice->due_date->format('d M Y') }}</div>
            </div>
            @if ($invoice->paid_at)
                <div class="date-box">
                    <div class="label">{{ __('Payment Date') }}</div>
                    <div class="value">{{ $invoice->paid_at->format('d M Y') }}</div>
                </div>
            @endif
        </div>

        {{-- Line Items Table --}}
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
            <div class="vat-notice exempt">
                {!! nl2br(e($invoice->vat_exempt_notice)) !!}
            </div>
        @elseif($vatType === 'reverse_charge')
            <div class="vat-notice reverse-charge">
                {!! __(
                    'VAT Reverse Charge — VAT is to be accounted for by the recipient pursuant to Art. 196 of Council Directive 2006/112/EC.',
                ) !!}
            </div>
        @elseif($vatType === 'oss')
            <div class="vat-notice oss">
                {!! __('OSS Scheme — VAT applied at the seller\'s country rate under the EU One-Stop-Shop scheme.') !!}
            </div>
        @elseif($vatType === 'exempt')
            <div class="vat-notice exempt">
                {!! __('VAT Exempt — Supply is exempt from VAT (buyer located outside the EU).') !!}
            </div>
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
                        <td>
                            {{ __('VAT') }}
                            @if ($invoice->vat_rate > 0)
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
            {{ $invoice->invoice_number }} · {{ __('Generated by') }} InvoiceKit ·
            {{ now()->format('d M Y') }}
        </div>

    </div>
</body>

</html>
