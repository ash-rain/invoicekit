<?php

namespace App\Services;

use App\Exceptions\NoAvailableApiKeyException;
use App\Models\AiApiKey;

class AiKeyRotationService
{
    public function getNextKey(string $provider = 'gemini'): AiApiKey
    {
        $key = AiApiKey::available()
            ->where('provider', $provider)
            ->orderByRaw('last_used_at IS NOT NULL, last_used_at ASC')
            ->first();

        if ($key === null) {
            throw new NoAvailableApiKeyException($provider);
        }

        return $key;
    }

    public function markUsed(AiApiKey $key): void
    {
        $key->update([
            'last_used_at' => now(),
            'request_count' => $key->request_count + 1,
            'last_error_at' => null,
            'last_error_message' => null,
        ]);
    }

    public function markFailed(AiApiKey $key, string $error): void
    {
        $key->update([
            'last_error_at' => now(),
            'last_error_message' => $error,
        ]);
    }

    public function clearError(AiApiKey $key): void
    {
        $key->update([
            'last_error_at' => null,
            'last_error_message' => null,
        ]);
    }
}
