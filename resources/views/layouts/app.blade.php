<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'InvoiceKit') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside class="w-64 bg-indigo-900 text-white flex flex-col shrink-0">
                <!-- Logo -->
                <div class="flex items-center h-16 px-6 border-b border-indigo-800">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold tracking-tight">
                        InvoiceKit
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                    <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="grid">
                        {{ __('Dashboard') }}
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('timer')" :active="request()->routeIs('timer')" icon="clock">
                        {{ __('Timer') }}
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('clients.index')" :active="request()->routeIs('clients.*')" icon="users">
                        {{ __('Clients') }}
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" icon="folder">
                        {{ __('Projects') }}
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" icon="document">
                        {{ __('Invoices') }}
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('billing.index')" :active="request()->routeIs('billing.*')" icon="credit-card">
                        {{ __('Billing') }}
                    </x-sidebar-link>
                </nav>

                <!-- User -->
                <div class="px-4 py-4 border-t border-indigo-800">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-medium">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-indigo-300 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-indigo-400 hover:text-white transition-colors" title="Logout">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Main content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top bar -->
                <header class="h-16 bg-white border-b border-gray-200 flex items-center px-6 shrink-0">
                    @isset($header)
                        <h1 class="text-xl font-semibold text-gray-900">{{ $header }}</h1>
                    @endisset
                </header>

                <!-- Page content -->
                <main class="flex-1 overflow-y-auto p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
        @stack('scripts')

        {{-- Cookie consent banner (first-party session cookies only) --}}
        <div id="cookie-banner"
             class="fixed bottom-0 inset-x-0 z-50 bg-gray-900 text-white px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-lg"
             style="display:none!important">
            <p class="text-sm leading-relaxed max-w-2xl">
                We use only essential first-party session cookies required for authentication. No tracking or advertising cookies are used.
                <a href="{{ url('/privacy') }}" class="underline ml-1">Privacy Policy</a>
            </p>
            <button onclick="document.getElementById('cookie-banner').style.setProperty('display','none','important');localStorage.setItem('ik_cookie_consent','1')"
                    class="shrink-0 px-4 py-2 bg-indigo-500 hover:bg-indigo-400 rounded-lg text-sm font-medium">
                Got it
            </button>
        </div>
        <script>
            if (!localStorage.getItem('ik_cookie_consent')) {
                document.getElementById('cookie-banner').style.removeProperty('display');
            }
        </script>
    </body>
</html>
