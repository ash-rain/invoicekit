<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy | InvoiceKit</title>
    <meta name="description" content="InvoiceKit Privacy Policy — how we collect, use, and protect your data in accordance with GDPR.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; font-family: Figtree, ui-sans-serif, system-ui, sans-serif; background: #f9fafb; color: #111827; }
        nav { background: #4f46e5; padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; }
        nav a { color: #fff; text-decoration: none; font-weight: 700; font-size: 1.25rem; }
        nav .back { font-size: .9rem; font-weight: 500; opacity: .8; }
        nav .back:hover { opacity: 1; }
        .container { max-width: 800px; margin: 0 auto; padding: 3rem 1.5rem; }
        h1 { font-size: 2rem; font-weight: 800; margin-bottom: .5rem; }
        .updated { color: #6b7280; font-size: .9rem; margin-bottom: 2rem; }
        h2 { font-size: 1.25rem; font-weight: 700; margin: 2rem 0 .5rem; color: #1f2937; }
        p, li { line-height: 1.75; color: #374151; }
        ul { padding-left: 1.5rem; }
        a { color: #4f46e5; }
        footer { text-align: center; padding: 2rem; color: #9ca3af; font-size: .875rem; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ url('/') }}">InvoiceKit</a>
        <a href="{{ url('/') }}" class="back">← Back to Home</a>
    </nav>

    <div class="container">
        <h1>Privacy Policy</h1>
        <p class="updated">Last updated: March 2026</p>

        <p>InvoiceKit ("we", "our", or "us") is committed to protecting your personal data and complying with the General Data Protection Regulation (GDPR) and applicable EU privacy law. This policy explains what data we collect, why, and how you can exercise your rights.</p>

        <h2>1. Data We Collect</h2>
        <ul>
            <li><strong>Account data:</strong> name, email address, password (hashed).</li>
            <li><strong>Business data:</strong> clients, invoices, time entries, and project details you enter.</li>
            <li><strong>Payment data:</strong> billing information processed by Stripe (we never store full card numbers).</li>
            <li><strong>Technical data:</strong> IP address, browser user-agent, session identifiers.</li>
        </ul>

        <h2>2. Legal Basis for Processing</h2>
        <p>We process your data under the following lawful bases:</p>
        <ul>
            <li><strong>Contract performance:</strong> to provide the invoicing and time-tracking service.</li>
            <li><strong>Legitimate interests:</strong> to improve the service and prevent fraud.</li>
            <li><strong>Legal obligation:</strong> VAT records may be required by EU tax law.</li>
            <li><strong>Consent:</strong> for optional marketing communications.</li>
        </ul>

        <h2>3. Data Retention</h2>
        <p>Invoice and accounting data is retained for 7 years in accordance with EU VAT regulations. Account data is deleted within 30 days of account closure, except where legal retention obligations apply.</p>

        <h2>4. Third-Party Processors</h2>
        <ul>
            <li><strong>Stripe</strong> — payment processing (Stripe's <a href="https://stripe.com/en-gb/privacy" rel="noopener">privacy policy</a>).</li>
            <li><strong>Hetzner</strong> — EU-based hosting (Germany).</li>
        </ul>

        <h2>5. Your Rights (GDPR)</h2>
        <p>You have the right to: access your data, correct inaccuracies, request erasure (right to be forgotten), restrict or object to processing, and data portability. Contact us at <a href="mailto:privacy@invoicekit.eu">privacy@invoicekit.eu</a> to exercise any of these rights.</p>

        <h2>6. Cookies</h2>
        <p>We use only first-party session cookies essential for authentication. We do not use third-party tracking or advertising cookies.</p>

        <h2>7. Contact</h2>
        <p>Data controller: InvoiceKit. Email: <a href="mailto:privacy@invoicekit.eu">privacy@invoicekit.eu</a>.</p>
    </div>

    <footer>
        &copy; {{ date('Y') }} InvoiceKit &mdash;
        <a href="{{ url('/privacy') }}">Privacy</a> &middot;
        <a href="{{ url('/terms') }}">Terms</a>
    </footer>
</body>
</html>
