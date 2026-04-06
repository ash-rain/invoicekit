<?php

namespace App\Filament\Resources\AiApiKeys\Pages;

use App\Filament\Resources\AiApiKeys\AiApiKeyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAiApiKeys extends ListRecords
{
    protected static string $resource = AiApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
