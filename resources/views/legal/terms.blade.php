<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service | InvoiceKit</title>
    <meta name="description" content="InvoiceKit Terms of Service — the rules governing use of the InvoiceKit platform.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Figtree, ui-sans-serif, system-ui, sans-serif;
            background: #f9fafb;
            color: #111827;
        }

        nav {
            background: #4f46e5;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.25rem;
        }

        nav .back {
            font-size: .9rem;
            font-weight: 500;
            opacity: .8;
        }

        nav .back:hover {
            opacity: 1;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: .5rem;
        }

        .updated {
            color: #6b7280;
            font-size: .9rem;
            margin-bottom: 2rem;
        }

        h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 2rem 0 .5rem;
            color: #1f2937;
        }

        p,
        li {
            line-height: 1.75;
            color: #374151;
        }

        ul {
            padding-left: 1.5rem;
        }

        a {
            color: #4f46e5;
        }

        footer {
            text-align: center;
            padding: 2rem;
            color: #9ca3af;
            font-size: .875rem;
        }
    </style>

    @if (config('services.google.analytics_id'))
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '{{ config('services.google.analytics_id') }}');
        </script>
    @endif
</head>

<body>
    <nav>
        <a href="{{ url('/') }}">InvoiceKit</a>
        <a href="{{ url('/') }}" class="back">← Back to Home</a>
    </nav>

    <div class="container">
        <h1>Terms of Service</h1>
        <p class="updated">Last updated: April 2026</p>

        <p>These Terms of Service ("Terms") govern your use of InvoiceKit (the "Service"). By creating an account you
            agree to these Terms.</p>

        <h2>1. Service Operator</h2>
        <p>InvoiceKit is operated by <strong>NetShell Ltd.</strong> ("we", "us", "our"). NetShell is the data
            controller, the contracting party for your subscription, and the entity responsible for the Service under
            applicable law. References to "InvoiceKit" throughout these Terms refer to NetShell Ltd. acting as operator
            of the Service.</p>

        <h2>2. Description of Service</h2>
        <p>InvoiceKit is a SaaS platform for invoicing and time tracking designed for EU-based freelancers and small
            businesses. Features vary by subscription plan.</p>

        <h2>3. Account Responsibilities</h2>
        <ul>
            <li>You must provide accurate registration information.</li>
            <li>You are responsible for keeping your password secure.</li>
            <li>You may not share your account with others.</li>
            <li>You must be at least 18 years old to use the Service.</li>
        </ul>

        <h2>4. Subscription Plans and Billing</h2>
        <p>InvoiceKit offers Free, Starter, and Pro plans. Current pricing is displayed on the <a href="{{ url('/#pricing') }}">pricing page</a> and within the application. Subscriptions renew automatically. You may cancel at any time; cancellation takes effect at the end of the billing period.</p>

        <h2>5. Payment Processing — Stripe</h2>
        <p>Subscription payments are processed by <strong>Stripe, Inc.</strong> Stripe is an independent third-party payment processor. NetShell is not responsible for Stripe's systems, availability, or any errors in payment processing. Your payment data is held by Stripe under <a href="https://stripe.com/privacy" target="_blank" rel="noopener">Stripe's Privacy Policy</a> and is never stored on InvoiceKit's servers. Disputes about charges should be directed to <a href="mailto:billing@invoicekit.eu">billing@invoicekit.eu</a>; we will liaise with Stripe on your behalf where possible.</p>

        <h2>6. Infrastructure — Hetzner</h2>
        <p>The Service is hosted on servers provided by <strong>Hetzner Online GmbH</strong>. Hetzner is responsible for the physical infrastructure and data-centre operations. NetShell is not liable for outages, data loss, or service interruptions caused by Hetzner's infrastructure failures, though we will make reasonable efforts to maintain availability and communicate incidents promptly.</p>

        <h2>7. Acceptable Use</h2>
        <p>You may not use the Service for any unlawful purpose, including VAT fraud. You may not reverse-engineer,
            resell, or redistribute the Service.</p>

        <h2>8. Data and Privacy</h2>
        <p>We handle your data in accordance with our <a href="{{ url('/privacy') }}">Privacy Policy</a>. You own your
            business data; we process it only to provide the Service.</p>

        <h2>9. VAT Compliance Notice</h2>
        <p>InvoiceKit automates VAT calculations based on the rules you configure. You remain responsible for the
            accuracy of VAT information submitted to tax authorities. InvoiceKit is not a tax advisor.</p>

        <h2>10. Limitation of Liability</h2>
        <p>To the maximum extent permitted by law, NetShell Ltd. is not liable for indirect, incidental, or
            consequential damages arising from your use of the Service, including any issues originating from
            third-party providers such as Stripe or Hetzner.</p>

        <h2>11. Governing Law</h2>
        <p>These Terms are governed by the laws of the jurisdiction in which NetShell Ltd. is registered. Any disputes
            shall be subject to the exclusive jurisdiction of the courts of that jurisdiction.</p>

        <h2>12. Contact</h2>
        <p>For questions about these Terms, contact <a href="mailto:legal@invoicekit.eu">legal@invoicekit.eu</a>.</p>
    </div>

    <footer>
        &copy; {{ date('Y') }} InvoiceKit &mdash;
        <a href="{{ url('/privacy') }}">Privacy</a> &middot;
        <a href="{{ url('/terms') }}">Terms</a>
    </footer>
</body>

</html>
