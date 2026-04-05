<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'InvoiceKit Blog')</title>
    <meta name="description" content="@yield('meta_description', 'Insights and updates from InvoiceKit.')">
    <link rel="canonical" href="@yield('canonical', request()->url())">

    {{-- Open Graph --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', request()->url())">
    <meta property="og:title" content="@yield('og_title', 'InvoiceKit Blog')">
    <meta property="og:description" content="@yield('og_description', 'Insights and updates from InvoiceKit.')">
    <meta property="og:image" content="@yield('og_image', url('/images/og-thumb.png'))">
    <meta property="og:locale" content="en_GB">
    @foreach (config('invoicekit.supported_languages', []) as $altLang)
        <meta property="og:locale:alternate" content="{{ config('invoicekit.og_locales.' . $altLang, 'en_GB') }}">
    @endforeach

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'InvoiceKit Blog')">
    <meta name="twitter:description" content="@yield('og_description', 'Insights and updates from InvoiceKit.')">
    <meta name="twitter:image" content="@yield('og_image', url('/images/og-thumb.png'))">

    @yield('structured_data')

    {{-- RSS Feed --}}
    <link rel="alternate" type="application/rss+xml" title="InvoiceKit Blog" href="{{ route('blog.feed') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>

<body class="bg-white text-gray-900 font-sans antialiased">

    <nav class="border-b border-gray-100 bg-white sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 font-bold text-gray-900 text-lg">
                <img src="/img/logo.png" alt="InvoiceKit" class="h-7 w-7">
                InvoiceKit
            </a>
            <div class="flex items-center gap-6 text-sm font-medium text-gray-600">
                <a href="/#features" class="hover:text-gray-900 transition-colors">Features</a>
                <a href="/#pricing" class="hover:text-gray-900 transition-colors">Pricing</a>
                <a href="{{ route('blog.index') }}"
                    class="hover:text-gray-900 transition-colors {{ request()->routeIs('blog.*') ? 'text-indigo-600 font-semibold' : '' }}">Blog</a>
                <a href="/login" class="hover:text-gray-900 transition-colors">Login</a>
                <a href="/register"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Get Started →
                </a>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-gray-100 mt-24 py-12">
        <div
            class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500">
            <p>© {{ date('Y') }} InvoiceKit. All rights reserved.</p>
            <div class="flex items-center gap-6">
                <a href="{{ route('blog.index') }}" class="hover:text-gray-900 transition-colors">Blog</a>
                <a href="{{ route('blog.feed') }}" class="hover:text-gray-900 transition-colors">RSS Feed</a>
                <a href="{{ route('privacy') }}" class="hover:text-gray-900 transition-colors">Privacy</a>
                <a href="{{ route('terms') }}" class="hover:text-gray-900 transition-colors">Terms</a>
            </div>
        </div>
    </footer>
</body>

</html>
