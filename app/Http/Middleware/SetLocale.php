<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('invoicekit.supported_languages', ['en']);

        // Prefer the authenticated user's stored locale preference, then
        // fall back to the session value, and finally to the app default.
        $locale = (Auth::check() && Auth::user()->locale)
            ? Auth::user()->locale
            : session('locale', config('app.locale'));

        if (in_array($locale, $supported)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
