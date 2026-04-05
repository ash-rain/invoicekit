<!DOCTYPE html>
<html lang="{{ $invoice->language ?? 'en' }}">

<head>
    <meta charset="UTF-8" />
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10.5pt;
            color: #1e293b;
            background: #fff;
            line-height: 1.55;
        }

        .page { padding: 44px 56px; }

        /* Header */
        .header {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
        }

        .brand { font-size: 20pt; font-weight: bold; color: #0f172a; letter-spacing: -0.5px; }

        .invoice-meta { text-align: right; }
        .invoice-meta .label {
            font-size: 8pt; text-transform: uppercase; letter-spacing: 0.12em;
            color: #94a3b8; font-weight: bold;
        }
        .invoice-meta .number { font-size: 14pt; font-weight: bold; color: #0f172a; margin-top: 3px; }

        /* Divider */
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 0 0 28px 0; }

        /* Parties */
        .parties { width: 100%; margin-bottom: 32px; border-collapse: collapse; }
        .party { width: 50%; vertical-align: top; }
        .party-left { padding-right: 24px; }
        .party-right { padding-left: 24px; border-left: 1px solid #e2e8f0; }

        .party-label {
            font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.1em;
            color: #94a3b8; font-weight: bold; margin-bottom: 8px;
        }
        .party-name { font-size: 12pt; font-weight: bold; color: #0f172a; margin-bottom: 5px; }
        .party-detail { font-size: 9pt; color: #475569; line-height: 1.65; }

        .vat-badge {
            display: inline-block; background: #f1f5f9; color: #475569;
            border-radius: 3px; padding: 1px 6px; font-size: 8pt; font-weight: bold; margin-top: 3px;
        }

        /* Dates */
        .dates-row { width: 100%; margin-bottom: 32px; border-collapse: collapse; }
        .date-cell { padding-right: 16px; vertical-align: top; }
        .date-label {
            font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.1em;
            color: #94a3b8; font-weight: bold;
        }
        .date-value { font-size: 10.5pt; font-weight: bold; color: #0f172a; margin-top: 2px; }

        /* Status chip */
        .status-paid-chip {
            display: inline-block; background: #f0fdf4; color: #16a34a;
            border: 1px solid #bbf7d0; border-radius: 20px;
            padding: 2px 12px; font-size: 8.5pt; font-weight: bold;
        }
        .status-cancelled-chip {
            display: inline-block; background: #fef2f2; color: #dc2626;
            border: 1px solid #fecaca; border-radius: 20px;
            padding: 2px 12px; font-size: 8.5pt; font-weight: bold;
        }

        /* Items */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items thead th {
            padding: 6px 10px; font-size: 8pt; text-transform: uppercase;
            letter-spacing: 0.08em; color: #94a3b8; font-weight: bold;
            border-bottom: 2px solid #e2e8f0; text-align: left;
        }
        table.items thead th.right { text-align: right; }
        table.items tbody td {
            padding: 9px 10px; font-size: 9.5pt; border-bottom: 1px solid #f1f5f9; vertical-align: top;
        }
        table.items tbody td.right { text-align: right; }
        table.items tfoot td {
            padding: 6px 10px; font-size: 9.5pt; color: #475569;
        }
        table.items tfoot td.right { text-align: right; }

        /* Totals */
        .totals { width: 280px; margin-left: auto; margin-bottom: 28px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals table td { padding: 5px 0; font-size: 9.5pt; color: #475569; }
        .totals table td.right { text-align: right; }
        .totals .grand-total td {
            font-size: 12pt; font-weight: bold; color: #0f172a;
            border-top: 2px solid #0f172a; padding-top: 10px;
        }

        /* VAT notice */
        .vat-notice { margin-bottom: 20px; padding: 10px 14px; border-radius: 4px; font-size: 9pt; line-height: 1.5; }
        .vat-notice.reverse-charge { background: #fffbeb; border-left: 3px solid #f59e0b; color: #92400e; }
        .vat-notice.oss { background: #eff6ff; border-left: 3px solid #3b82f6; color: #1e3a8a; }
        .vat-notice.exempt { background: #f0fdf4; border-left: 3px solid #22c55e; color: #14532d; }

        /* Notes */
        .notes-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px 14px; font-size: 9pt; color: #475569; margin-bottom: 24px; }
        .notes-label { font-weight: bold; color: #334155; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 5px; }

        /* Footer */
        .footer { margin-top: 36px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 8pt; color: #94a3b8; text-align: center; }
    </style>
</head>

<body>
    <div class="page">

        {{-- Header --}}
        <table class="header" cellpadding="0" cellspacing="0">
            <tr>
                <td style="vertical-align:top;">
                    @if ($company?->logoUrl())
                        <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}" style="max-height:44px; max-width:160px;">
                    @else
                        <div class="brand">{{ $company?->name ?? $invoice->user->name }}</div>
                    @endif
                </td>
                <td style="vertical-align:top; text-align:right;">
                    <div class="invoice-meta">
                        @php
                            $docLabel = match($invoice->document_type ?? 'invoice') {
                                'credit_note' => __('Credit Note'),
                                'debit_note'  => __('Debit Note'),
                                'proforma'    => __('Proforma Invoice'),
                                default       => __('Invoice'),
                            };
                        @endphp
                        <div class="label">{{ $docLabel }}</div>
                        <div class="number">{{ $invoice->invoice_number }}</div>
                        @if ($invoice->status === 'paid')
                            <div style="margin-top:6px;">
                                <span class="status-paid-chip">&#10003; {{ __('Paid') }}</span>
                            </div>
                        @elseif ($invoice->status === 'cancelled')
                            <div style="margin-top:6px;">
                                <span class="status-cancelled-chip">&#10007; {{ __('Cancelled') }}</span>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <hr class="divider">

        {{-- Parties --}}
        <table class="parties" cellpadding="0" cellspacing="0">
            <tr>
                <td class="party party-left">
                    <div class="party-label">{{ __('From') }}</div>
                    @php $company = $invoice->user->currentCompany; @endphp
                    <div class="party-name">{{ $company?->name ?? $invoice->user->name }}</div>
                    <div class="party-detail">
                        @if ($company)
                            @if ($company->address_line1){{ $company->address_line1 }}<br>@endif
                            @if ($company->address_line2){{ $company->address_line2 }}<br>@endif
                            @if ($company->postal_code || $company->city){{ implode(' ', array_filter([$company->postal_code, $company->city])) }}<br>@endif
                            @if ($company->country){{ $company->country }}<br>@endif
                            @if ($company->vat_number)<span class="vat-badge">VAT: {{ $company->vat_number }}</span><br>@endif
                            @if ($company->registration_number)Reg: {{ $company->registration_number }}<br>@endif
                            @if ($company->bank_iban)
                                @if ($company->bank_name){{ $company->bank_name }}<br>@endif
                                IBAN: {{ $company->bank_iban }}<br>
                                @if ($company->bank_bic)BIC: {{ $company->bank_bic }}<br>@endif
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
                        @if ($invoice->client->address){!! nl2br(e($invoice->client->address)) !!}<br>@endif
                        {{ $invoice->client->country }}
                        @if ($invoice->client->email)<br>{{ $invoice->client->email }}@endif
                        @if ($invoice->client->vat_number)<br><span class="vat-badge">VAT: {{ $invoice->client->vat_number }}</span>@endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Dates --}}
        <table class="dates-row" cellpadding="0" cellspacing="0">
            <tr>
                <td class="date-cell">
                    <div class="date-label">{{ __('Issue Date') }}</div>
                    <div class="date-value">{{ $invoice->issue_date->format('d M Y') }}</div>
                </td>
                <td class="date-cell">
                    <div class="date-label">{{ __('Due Date') }}</div>
                    <div class="date-value">{{ $invoice->due_date->format('d M Y') }}</div>
                </td>
                @if ($invoice->paid_at)
                <td class="date-cell">
                    <div class="date-label">{{ __('Payment Date') }}</div>
                    <div class="date-value">{{ $invoice->paid_at->format('d M Y') }}</div>
                </td>
                @endif
            </tr>
        </table>

        <hr class="divider">

        {{-- Line Items --}}
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
                    <td class="right">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}</td>
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
            <div class="vat-notice reverse-charge">{!! __('VAT Reverse Charge — VAT is to be accounted for by the recipient pursuant to Art. 196 of Council Directive 2006/112/EC.') !!}</div>
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
                    <td>{{ __('VAT') }}@if ($invoice->vat_rate > 0) ({{ $invoice->vat_rate }}%)@endif</td>
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
            {{ $invoice->invoice_number }} &middot; {{ __('Generated by') }} InvoiceKit &middot; {{ now()->format('d M Y') }}
        </div>

    </div>
</body>
</html>
