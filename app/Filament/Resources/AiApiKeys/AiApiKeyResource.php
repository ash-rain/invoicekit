<?php

namespace App\Filament\Resources\AiApiKeys;

use App\Filament\Resources\AiApiKeys\Pages\CreateAiApiKey;
use App\Filament\Resources\AiApiKeys\Pages\EditAiApiKey;
use App\Filament\Resources\AiApiKeys\Pages\ListAiApiKeys;
use App\Filament\Resources\AiApiKeys\Schemas\AiApiKeyForm;
use App\Filament\Resources\AiApiKeys\Tables\AiApiKeysTable;
use App\Models\AiApiKey;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AiApiKeyResource extends Resource
{
    protected static ?string $model = AiApiKey::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'API Keys';

    protected static string|\UnitEnum|null $navigationGroup = 'AI Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AiApiKeyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AiApiKeysTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiApiKeys::route('/'),
            'create' => CreateAiApiKey::route('/create'),
            'edit' => EditAiApiKey::route('/{record}/edit'),
        ];
    }
}
