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
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-xl font-bold tracking-tight">
                    <img src="/img/logo.png" alt="{{ config('app.name', 'InvoiceKit') }}" class="h-7 w-auto">
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
                    <div
                        class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-medium">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-indigo-300 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-indigo-400 hover:text-white transition-colors"
                            title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 shrink-0">
                @isset($header)
                    <h1 class="text-xl font-semibold text-gray-900">{{ $header }}</h1>
                @else
                    <div></div>
                @endisset

                @php
                    $supportedLanguages = config('invoicekit.supported_languages', ['en']);
                    $locales = [
                        'bg' => ['name' => 'Български', 'flag' => '🇧🇬'],
                        'cs' => ['name' => 'Čeština', 'flag' => '🇨🇿'],
                        'da' => ['name' => 'Dansk', 'flag' => '🇩🇰'],
                        'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪'],
                        'el' => ['name' => 'Ελληνικά', 'flag' => '🇬🇷'],
                        'en' => ['name' => 'English', 'flag' => '🇬🇧'],
                        'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
                        'et' => ['name' => 'Eesti', 'flag' => '🇪🇪'],
                        'fi' => ['name' => 'Suomi', 'flag' => '🇫🇮'],
                        'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
                        'ga' => ['name' => 'Gaeilge', 'flag' => '🇮🇪'],
                        'hr' => ['name' => 'Hrvatski', 'flag' => '🇭🇷'],
                        'hu' => ['name' => 'Magyar', 'flag' => '🇭🇺'],
                        'it' => ['name' => 'Italiano', 'flag' => '🇮🇹'],
                        'lt' => ['name' => 'Lietuvių', 'flag' => '🇱🇹'],
                        'lv' => ['name' => 'Latviešu', 'flag' => '🇱🇻'],
                        'mt' => ['name' => 'Malti', 'flag' => '🇲🇹'],
                        'nl' => ['name' => 'Nederlands', 'flag' => '🇳🇱'],
                        'pl' => ['name' => 'Polski', 'flag' => '🇵🇱'],
                        'pt' => ['name' => 'Português', 'flag' => '🇵🇹'],
                        'ro' => ['name' => 'Română', 'flag' => '🇷🇴'],
                        'sk' => ['name' => 'Slovenčina', 'flag' => '🇸🇰'],
                        'sl' => ['name' => 'Slovenščina', 'flag' => '🇸🇮'],
                        'sv' => ['name' => 'Svenska', 'flag' => '🇸🇪'],
                    ];
                    $currentLocale = app()->getLocale();
                    $currentLocaleData = $locales[$currentLocale] ?? [
                        'name' => strtoupper($currentLocale),
                        'flag' => '🌐',
                    ];
                @endphp

                <div x-data="{ open: false, search: '' }" class="relative">
                    {{-- Trigger button --}}
                    <button @click="open = !open; if (open) $nextTick(() => $refs.search.focus())"
                        @click.outside="open = false; search = ''"
                        class="flex items-center gap-2 h-9 px-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 text-sm text-gray-700 font-medium transition-colors shadow-sm">
                        <span class="text-base leading-none">{{ $currentLocaleData['flag'] }}</span>
                        <span class="text-sm font-medium text-gray-700">{{ $currentLocaleData['name'] }}</span>
                        <span
                            class="uppercase tracking-wide text-xs font-semibold text-gray-400">{{ $currentLocale }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown panel --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden">

                        {{-- Search --}}
                        <div class="px-3 pt-3 pb-2 border-b border-gray-100">
                            <div class="relative">
                                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                                </svg>
                                <input x-ref="search" x-model="search" type="text" placeholder="Search language…"
                                    class="w-full pl-8 pr-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                        </div>

                        {{-- Language grid --}}
                        <div class="p-2 grid grid-cols-2 gap-0.5 max-h-64 overflow-y-auto">
                            @foreach ($supportedLanguages as $locale)
                                @php
                                    $data = $locales[$locale] ?? ['name' => strtoupper($locale), 'flag' => '🌐'];
                                    $isActive = $locale === $currentLocale;
                                @endphp
                                <form method="POST" action="{{ route('locale.switch') }}"
                                    x-show="search === '' || '{{ strtolower($data['name']) }} {{ $locale }}'.includes(search.toLowerCase())">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $locale }}">
                                    <button type="submit"
                                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors text-left
                                                   {{ $isActive
                                                       ? 'bg-indigo-50 text-indigo-700 font-medium ring-1 ring-inset ring-indigo-200'
                                                       : 'text-gray-700 hover:bg-gray-50' }}">
                                        <span class="text-lg leading-none shrink-0">{{ $data['flag'] }}</span>
                                        <span class="truncate">{{ $data['name'] }}</span>
                                        @if ($isActive)
                                            <svg class="ml-auto w-3.5 h-3.5 text-indigo-500 shrink-0"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
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
            We use only essential first-party session cookies required for authentication. No tracking or advertising
            cookies are used.
            <a href="{{ url('/privacy') }}" class="underline ml-1">Privacy Policy</a>
        </p>
        <button
            onclick="document.getElementById('cookie-banner').style.setProperty('display','none','important');localStorage.setItem('ik_cookie_consent','1')"
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
