<?php

namespace App\Exceptions;

use RuntimeException;

class NoAvailableApiKeyException extends RuntimeException
{
    public function __construct(string $provider = 'gemini')
    {
        parent::__construct("No available {$provider} API keys. All keys are inactive or in cooldown.");
    }
}
