<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class TurnstileService
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Verify a Turnstile token against Cloudflare's siteverify endpoint.
     */
    public function verify(string $token, ?string $ip = null): bool
    {
        try {
            $response = Http::asForm()->timeout(10)->post(self::VERIFY_URL, [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]);
        } catch (ConnectionException) {
            return false;
        }

        if ($response->failed()) {
            return false;
        }

        return $response->json('success') === true;
    }
}
