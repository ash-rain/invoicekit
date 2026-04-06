<?php

namespace App\Filament\Resources\AiApiKeys\Pages;

use App\Filament\Resources\AiApiKeys\AiApiKeyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAiApiKey extends CreateRecord
{
    protected static string $resource = AiApiKeyResource::class;
}
