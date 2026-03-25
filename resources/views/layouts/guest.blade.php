<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen bg-indigo-950 flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <!-- Branding -->
            <div class="flex items-center justify-center gap-3 mb-8">
                <x-application-logo class="w-9 h-9 fill-current text-indigo-300" />
                <a href="/" class="text-2xl font-bold tracking-tight text-white">
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
