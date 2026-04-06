<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gemini AI Configuration
    |--------------------------------------------------------------------------
    */

    'gemini' => [
        'endpoint' => env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Import Limits
    |--------------------------------------------------------------------------
    |
    | Daily per-user limits for AI-powered document imports, keyed by plan.
    | null means unlimited. These values are used throughout the app and
    | on the landing page — change them here to update everywhere.
    |
    | Users who provide their own Gemini API key in Settings → AI bypass
    | all app-enforced limits entirely, using their own free quota.
    |
    */

    'limits' => [
        'free' => env('AI_LIMIT_FREE', 2),
        'starter' => env('AI_LIMIT_STARTER', 50),
        'pro' => null,

        /*
        | Hard cap on the total number of daily AI imports that may use the
        | system (admin-managed) API keys. Prevents unexpected Gemini API
        | charges by keeping system usage within the free tier.
        | Set to null to disable the system cap.
        */
        'system_daily_cap' => env('AI_SYSTEM_DAILY_CAP', 1000),
    ],

];
