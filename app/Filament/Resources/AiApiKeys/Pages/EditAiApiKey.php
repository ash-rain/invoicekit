<?php

namespace App\Filament\Resources\AiApiKeys\Pages;

use App\Filament\Resources\AiApiKeys\AiApiKeyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAiApiKey extends EditRecord
{
    protected static string $resource = AiApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
