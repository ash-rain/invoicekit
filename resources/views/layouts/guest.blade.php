<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=syne:400,500,600,700|dm-sans:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if (config('services.google.analytics_id'))
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '{{ config('services.google.analytics_id') }}');
        </script>
    @endif
</head>

<body class="antialiased" style="font-family:'DM Sans',sans-serif;background:#0f1117;">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <!-- Branding -->
            <div class="flex items-center justify-center mb-8">
                <a href="/" class="flex items-center gap-3 text-2xl font-bold tracking-tight text-white" style="font-family:'Syne',sans-serif;">
                    <img src="/img/logo.png" alt="{{ config('app.name', 'InvoiceKit') }}" class="h-8 w-auto">
                    {{ config('app.name', 'InvoiceKit') }}
                </a>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-2xl px-8 py-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>
