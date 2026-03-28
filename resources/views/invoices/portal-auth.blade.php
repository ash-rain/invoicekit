<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Invoice Portal') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=syne:400,500,600,700|dm-sans:400,500,600&display=swap"
        rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-[#0f1117]" style="font-family:'DM Sans',sans-serif;">

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <div class="flex items-center justify-center mb-8">
                <span class="text-2xl font-bold tracking-tight text-white" style="font-family:'Syne',sans-serif;">
                    {{ config('app.name', 'InvoiceKit') }}
                </span>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h1 class="text-xl font-bold text-[#0f1117] mb-1" style="font-family:'Syne',sans-serif;">
                    {{ __('Enter Password') }}
                </h1>
                <p class="text-sm text-gray-500 mb-6">
                    {{ __('This invoice is password-protected. Enter the password to view it.') }}
                </p>

                <form method="POST" action="{{ route('invoice.portal.auth', $accessToken->token) }}">
                    @csrf

                    <div class="mb-4">
                        <label for="password" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            {{ __('Password') }}
                        </label>
                        <input id="password" type="password" name="password" autofocus
                            class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('password') border-red-400 @enderror"
                            placeholder="{{ __('Enter password') }}">
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full px-4 py-2.5 text-sm font-bold bg-[#0f1117] text-white rounded-xl hover:bg-[#1a1f2e] transition">
                        {{ __('View Invoice') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>
