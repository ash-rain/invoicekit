<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice Reminder</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #1f2937;
        }
        .wrapper {
            max-width: 560px;
            margin: 32px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.07);
        }
        .header {
            background: #4f46e5;
            color: #fff;
            padding: 24px 32px;
        }
        .header h1 {
            margin: 0 0 4px;
            font-size: 20px;
            font-weight: 700;
        }
        .header p {
            margin: 0;
            font-size: 13px;
            opacity: .8;
        }
        .body {
            padding: 32px;
        }
        .body p {
            margin: 0 0 16px;
            font-size: 14px;
            line-height: 1.6;
            color: #374151;
        }
        .invoice-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px 20px;
            margin: 20px 0;
        }
        .invoice-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-box table td {
            padding: 4px 0;
            font-size: 13px;
        }
        .invoice-box table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #1f2937;
        }
        .invoice-box .total-row td {
            font-size: 15px;
            color: #4f46e5;
            font-weight: 700;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            margin-top: 4px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            margin: 16px 0;
            font-weight: 500;
        }
        .alert-warning { background: #fffbeb; color: #92400e; border-left: 4px solid #f59e0b; }
        .alert-danger  { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }
        .alert-info    { background: #eff6ff; color: #1e3a8a; border-left: 4px solid #3b82f6; }
        .footer {
            background: #f9fafb;
            padding: 16px 32px;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #f3f4f6;
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <h1>InvoiceKit</h1>
        <p>
            @if($reminderType === 'overdue')
                {{ __('Overdue invoice notice') }}
            @elseif($reminderType === 'due_today')
                {{ __('Payment due today') }}
            @else
                {{ __('Friendly payment reminder') }}
            @endif
        </p>
    </div>

    {{-- Body --}}
    <div class="body">

        <p>Hi {{ $invoice->client->name }},</p>

        @if($reminderType === 'overdue')
            <div class="alert alert-danger">
                ⚠ This invoice is <strong>{{ $invoice->due_date->diffInDays(now()) }} days overdue</strong>.
                Please arrange payment at your earliest convenience.
            </div>
            <p>
                We noticed that invoice <strong>{{ $invoice->invoice_number }}</strong> is past its due date.
                If you have already made the payment, please disregard this notice.
            </p>
        @elseif($reminderType === 'due_today')
            <div class="alert alert-warning">
                📅 Payment for this invoice is <strong>due today</strong>.
            </div>
            <p>
                This is a reminder that invoice <strong>{{ $invoice->invoice_number }}</strong>
                is due today, {{ $invoice->due_date->format('d F Y') }}.
            </p>
        @else
            <div class="alert alert-info">
                📋 Invoice <strong>{{ $invoice->invoice_number }}</strong> is due in
                <strong>{{ now()->diffInDays($invoice->due_date) }} days</strong>.
            </div>
            <p>
                This is a friendly reminder that the following invoice will be due on
                {{ $invoice->due_date->format('d F Y') }}.
            </p>
        @endif

        {{-- Invoice details --}}
        <div class="invoice-box">
            <table>
                <tr>
                    <td style="color:#6b7280">{{ __('Invoice Number') }}</td>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="color:#6b7280">{{ __('Issue Date') }}</td>
                    <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td style="color:#6b7280">{{ __('Due Date') }}</td>
                    <td>{{ $invoice->due_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td style="color:#6b7280">{{ __('Tax Base') }}</td>
                    <td>{{ formatCurrency($invoice->currency, (float)$invoice->subtotal) }}</td>
                </tr>
                @if((float)$invoice->vat_amount > 0)
                <tr>
                    <td style="color:#6b7280">{{ __('VAT') }} ({{ $invoice->vat_rate }}%)</td>
                    <td>{{ formatCurrency($invoice->currency, (float)$invoice->vat_amount) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>{{ __('Total Due') }}</td>
                    <td>{{ formatCurrency($invoice->currency, (float)$invoice->total) }}</td>
                </tr>
            </table>
        </div>

        @if($invoice->notes)
            <p style="color:#6b7280; font-size:13px"><em>{{ $invoice->notes }}</em></p>
        @endif

        <p>{{ __('The invoice PDF is attached to this email for your records.') }}</p>

        <p>
            {{ __('Thank you for your business!') }}<br>
            <strong>{{ $invoice->user->name }}</strong>
        </p>

    </div>

    {{-- Footer --}}
    <div class="footer">
        This email was sent by InvoiceKit on behalf of {{ $invoice->user->name }}.
        If you believe you received this in error, please contact {{ $invoice->user->email }}.
    </div>

</div>
</body>
</html>
