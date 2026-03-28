<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Failed</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; color: #1f2937; }
        .wrapper { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .header { background: #dc2626; color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0 0 4px; font-size: 20px; font-weight: 700; }
        .header p { margin: 0; font-size: 14px; opacity: 0.85; }
        .body { padding: 32px; }
        .body p { font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .cta { display: inline-block; background: #0f1117; color: #fff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-top: 8px; }
        .footer { padding: 16px 32px; background: #fafafa; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Payment Failed</h1>
            <p>Action required for your InvoiceKit subscription</p>
        </div>
        <div class="body">
            <p>Hi {{ $user->name }},</p>
            <p>We were unable to process your most recent subscription payment. Please update your payment method to keep your InvoiceKit plan active.</p>
            <p>If payment is not received within 3 days, your account will be downgraded to the Free plan.</p>
            <a href="{{ route('billing.index') }}" class="cta">Update Payment Method</a>
        </div>
        <div class="footer">
            InvoiceKit &mdash; You're receiving this because of a billing issue with your account.
        </div>
    </div>
</body>
</html>
