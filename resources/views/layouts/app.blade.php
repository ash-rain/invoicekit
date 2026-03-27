<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'InvoiceKit') }}</title>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f1117">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'InvoiceKit') }}">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    @auth
        <meta name="vapid-public-key" content="{{ config('services.webpush.public_key') }}">
    @endauth

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=syne:600,700,800|dm-sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased">

    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden" style="background:#f5f6fa;">

        {{-- ─── Backdrop (mobile only) ────────────────────────────── --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="sidebarOpen = false" x-cloak class="fixed inset-0 z-20 lg:hidden"
            style="background:rgba(0,0,0,0.6);backdrop-filter:blur(3px);"></div>

        {{-- ─── SIDEBAR ────────────────────────────────────────────── --}}
        <aside
            class="fixed inset-y-0 left-0 z-30 flex flex-col transition-transform duration-300 ease-in-out lg:relative lg:shrink-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            style="width:240px;background:#0f1117;">
            {{-- Logo --}}
            <div class="flex items-center gap-3 h-16 px-5 shrink-0"
                style="border-bottom:1px solid rgba(255,255,255,0.07);">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 flex-1 min-w-0">
                    <img src="/img/logo.png" alt="{{ config('app.name') }}"
                        class="h-7 w-auto brightness-0 invert shrink-0">
                    <span class="text-base font-bold text-white truncate"
                        style="font-family:'Syne',sans-serif;letter-spacing:-0.01em;">InvoiceKit</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg shrink-0"
                    style="color:rgba(255,255,255,0.3);" aria-label="Close navigation">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-5 space-y-0.5 overflow-y-auto">
                <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-[0.13em] select-none"
                    style="color:rgba(255,255,255,0.18);">Navigation</p>
                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                    icon="grid">{{ __('Dashboard') }}</x-sidebar-link>
                <x-sidebar-link :href="route('timer')" :active="request()->routeIs('timer')" icon="clock">{{ __('Timer') }}</x-sidebar-link>
                <x-sidebar-link :href="route('clients.index')" :active="request()->routeIs('clients.*')" icon="users">{{ __('Clients') }}</x-sidebar-link>
                <x-sidebar-link :href="route('projects.index')" :active="request()->routeIs('projects.*')"
                    icon="folder">{{ __('Projects') }}</x-sidebar-link>
                <x-sidebar-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')"
                    icon="document">{{ __('Invoices') }}</x-sidebar-link>
                <x-sidebar-link :href="route('billing.index')" :active="request()->routeIs('billing.*')"
                    icon="credit-card">{{ __('Billing') }}</x-sidebar-link>
            </nav>

            {{-- Notification bell --}}
            <div class="px-3 shrink-0" style="border-top:1px solid rgba(255,255,255,0.07);">
                <div class="pt-2">
                    <livewire:notification-bell />
                </div>
            </div>

            {{-- Language switcher --}}
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
                $currentLocaleData = $locales[$currentLocale] ?? ['name' => strtoupper($currentLocale), 'flag' => '🌐'];
            @endphp
            <div class="px-3 pb-2 shrink-0" style="border-top:1px solid rgba(255,255,255,0.07);">
                <div x-data="{ langOpen: false, langSearch: '' }" class="relative pt-2">
                    <button
                        @click="langOpen = !langOpen; if(langOpen) $nextTick(()=> $refs.langInput && $refs.langInput.focus())"
                        @click.outside="langOpen = false; langSearch = ''"
                        class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm transition-all"
                        style="color:rgba(255,255,255,0.4);"
                        onmouseover="this.style.background='rgba(255,255,255,0.06)';this.style.color='rgba(255,255,255,0.8)'"
                        onmouseout="this.style.background='';this.style.color='rgba(255,255,255,0.4)'">
                        <span class="text-base leading-none shrink-0">{{ $currentLocaleData['flag'] }}</span>
                        <span
                            class="flex-1 text-left text-xs font-medium truncate">{{ $currentLocaleData['name'] }}</span>
                        <span class="text-[9px] font-bold uppercase tracking-widest shrink-0"
                            style="color:rgba(255,255,255,0.2);">{{ strtoupper($currentLocale) }}</span>
                        <svg class="w-3 h-3 shrink-0 transition-transform duration-150"
                            :class="langOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            style="color:rgba(255,255,255,0.2);" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown — opens upward --}}
                    <div x-show="langOpen" x-cloak x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute bottom-full left-0 right-0 mb-1 rounded-xl overflow-hidden z-50"
                        style="background:#1a1d28;border:1px solid rgba(255,255,255,0.1);box-shadow:0 -24px 64px rgba(0,0,0,0.7);">
                        <div class="p-2" style="border-bottom:1px solid rgba(255,255,255,0.06);">
                            <div class="relative">
                                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3 h-3 pointer-events-none"
                                    style="color:rgba(255,255,255,0.25);" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                                </svg>
                                <input x-ref="langInput" x-model="langSearch" type="text"
                                    placeholder="{{ __('Search...') }}"
                                    class="w-full pl-7 pr-3 py-1.5 text-xs rounded-lg focus:outline-none"
                                    style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.8);border:1px solid rgba(255,255,255,0.09);">
                            </div>
                        </div>
                        <div class="p-1.5 grid grid-cols-2 gap-0.5 max-h-52 overflow-y-auto">
                            @foreach ($supportedLanguages as $locale)
                                @php
                                    $data = $locales[$locale] ?? ['name' => strtoupper($locale), 'flag' => 'X'];
                                    $isActive = $locale === $currentLocale;
                                @endphp
                                <form method="POST" action="{{ route('locale.switch') }}"
                                    x-show="langSearch === '' || '{{ strtolower($data['name']) }} {{ $locale }}'.includes(langSearch.toLowerCase())">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $locale }}">
                                    <button type="submit"
                                        class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-left transition-all"
                                        style="{{ $isActive ? 'background:rgba(245,158,11,0.15);color:#fbbf24;' : 'color:rgba(255,255,255,0.45);' }}"
                                        @if (!$isActive) onmouseover="this.style.background='rgba(255,255,255,0.06)';this.style.color='rgba(255,255,255,0.8)'"
                                    onmouseout="this.style.background='';this.style.color='rgba(255,255,255,0.45)'" @endif>
                                        <span class="text-sm leading-none shrink-0">{{ $data['flag'] }}</span>
                                        <span class="text-xs truncate">{{ $data['name'] }}</span>
                                        @if ($isActive)
                                            <svg class="ml-auto w-3 h-3 shrink-0" style="color:#f59e0b;"
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
            </div>

            {{-- User card --}}
            <div class="px-3 pb-4 shrink-0">
                <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl"
                    style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                        style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold truncate" style="color:rgba(255,255,255,0.85);">
                            {{ Auth::user()->name }}</p>
                        <p class="text-[10px] truncate mt-0.5" style="color:rgba(255,255,255,0.3);">
                            {{ Auth::user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg transition-all shrink-0"
                            style="color:rgba(255,255,255,0.22);" title="{{ __('Logout') }}"
                            onmouseover="this.style.color='rgba(255,255,255,0.7)';this.style.background='rgba(255,255,255,0.08)'"
                            onmouseout="this.style.color='rgba(255,255,255,0.22)';this.style.background=''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ─── MAIN CONTENT ───────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Mobile topbar (lg:hidden) --}}
            <header class="lg:hidden flex items-center gap-3 h-14 px-4 shrink-0"
                style="background:#0f1117;border-bottom:1px solid rgba(255,255,255,0.07);">
                <button @click="sidebarOpen = true" class="p-2 -ml-1 rounded-lg transition-all"
                    style="color:rgba(255,255,255,0.5);"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.color='white'"
                    onmouseout="this.style.background='';this.style.color='rgba(255,255,255,0.5)'"
                    aria-label="{{ __('Open navigation') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h11" />
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 select-none">
                    <img src="/img/logo.png" alt="{{ config('app.name') }}" class="h-6 w-auto brightness-0 invert">
                    <span class="text-[15px] font-bold text-white"
                        style="font-family:'Syne',sans-serif;">InvoiceKit</span>
                </a>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto" style="background:#f5f6fa;">
                {{ $slot }}
            </main>
        </div>

    </div>

    @livewireScripts
    @stack('scripts')

    {{-- Cookie consent banner --}}
    <div id="ik-cookie"
        class="fixed bottom-0 inset-x-0 z-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-6 py-4 sm:px-8"
        style="display:none!important;background:rgba(15,17,23,0.96);backdrop-filter:blur(16px);border-top:1px solid rgba(255,255,255,0.08);box-shadow:0 -8px 40px rgba(0,0,0,0.5);">
        <p class="text-sm leading-relaxed max-w-2xl" style="color:rgba(255,255,255,0.5);">
            We use only essential first-party session cookies required for authentication. No tracking or advertising
            cookies are used.
            <a href="{{ url('/privacy') }}" class="ml-1 underline" style="color:rgba(255,255,255,0.7);">Privacy
                Policy</a>
        </p>
        <button
            onclick="document.getElementById('ik-cookie').style.setProperty('display','none','important');localStorage.setItem('ik_cookie_consent','1')"
            class="shrink-0 px-5 py-2 rounded-xl text-sm font-bold" style="background:#f59e0b;color:#0f1117;"
            onmouseover="this.style.background='#fbbf24'" onmouseout="this.style.background='#f59e0b'">
            Got it
        </button>
    </div>
    <script>
        if (!localStorage.getItem('ik_cookie_consent')) {
            document.getElementById('ik-cookie').style.removeProperty('display');
        }
    </script>

    @auth
        {{-- Push notification permission prompt --}}
        <div id="ik-push-prompt"
            class="fixed bottom-0 inset-x-0 z-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 px-6 py-4 sm:px-8"
            style="display:none!important;background:rgba(15,17,23,0.96);backdrop-filter:blur(16px);border-top:1px solid rgba(255,255,255,0.08);box-shadow:0 -8px 40px rgba(0,0,0,0.5);">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" style="color:#f59e0b;" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.437L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="text-sm leading-relaxed" style="color:rgba(255,255,255,0.7);">
                    Enable push notifications to get alerted about invoice due dates and payments.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button id="ik-push-dismiss" class="px-4 py-2 rounded-xl text-sm font-medium transition-all"
                    style="color:rgba(255,255,255,0.4);border:1px solid rgba(255,255,255,0.1);"
                    onmouseover="this.style.color='rgba(255,255,255,0.7)'"
                    onmouseout="this.style.color='rgba(255,255,255,0.4)'">
                    Not now
                </button>
                <button id="ik-push-enable" class="px-5 py-2 rounded-xl text-sm font-bold"
                    style="background:#f59e0b;color:#0f1117;" onmouseover="this.style.background='#fbbf24'"
                    onmouseout="this.style.background='#f59e0b'">
                    Enable
                </button>
            </div>
        </div>
        <script>
            (function() {
                const DISMISS_KEY = 'ik_push_dismissed';
                const prompt = document.getElementById('ik-push-prompt');
                if (
                    prompt &&
                    'Notification' in window &&
                    'serviceWorker' in navigator &&
                    Notification.permission === 'default' &&
                    !localStorage.getItem(DISMISS_KEY)
                ) {
                    prompt.style.removeProperty('display');
                }
                document.getElementById('ik-push-dismiss')?.addEventListener('click', function() {
                    localStorage.setItem(DISMISS_KEY, '1');
                    prompt.style.setProperty('display', 'none', 'important');
                });
                document.getElementById('ik-push-enable')?.addEventListener('click', function() {
                    prompt.style.setProperty('display', 'none', 'important');
                    window.ikRequestPushPermission?.();
                });
            })
            ();
        </script>
    @endauth

</body>

</html>
