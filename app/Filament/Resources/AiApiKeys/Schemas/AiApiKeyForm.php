<?php

namespace App\Filament\Resources\AiApiKeys\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AiApiKeyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('API Key Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('label')
                            ->label('Label')
                            ->placeholder('e.g. Primary key, Fallback key')
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('provider')
                            ->label('Provider')
                            ->options([
                                'gemini' => 'Google Gemini',
                            ])
                            ->default('gemini')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        TextInput::make('api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(500)
                            ->placeholder('AIza...')
                            ->helperText('Leave blank to keep existing key when editing.')
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
