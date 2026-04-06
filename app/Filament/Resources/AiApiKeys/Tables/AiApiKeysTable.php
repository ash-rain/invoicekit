<?php

namespace App\Filament\Resources\AiApiKeys\Tables;

use App\Models\AiApiKey;
use App\Services\GeminiExtractionService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AiApiKeysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Label')
                    ->default('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('provider')
                    ->label('Provider')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'gemini' => 'Google Gemini',
                        default => ucfirst($state),
                    }),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('request_count')
                    ->label('Requests')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_used_at')
                    ->label('Last Used')
                    ->since()
                    ->sortable()
                    ->placeholder('Never'),
                TextColumn::make('last_error_at')
                    ->label('Last Error')
                    ->since()
                    ->sortable()
                    ->placeholder('None')
                    ->color('danger'),
                TextColumn::make('last_error_message')
                    ->label('Error')
                    ->limit(50)
                    ->placeholder('—')
                    ->tooltip(fn (AiApiKey $record): ?string => $record->last_error_message),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('test')
                    ->label('Test Key')
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Test API Key')
                    ->modalDescription('This will send a minimal request to the Gemini API to verify the key is valid.')
                    ->action(function (AiApiKey $record): void {
                        try {
                            /** @var GeminiExtractionService $service */
                            $service = app(GeminiExtractionService::class);
                            $service->testKey($record);

                            Notification::make()
                                ->title('Key is valid')
                                ->body('The API key responded successfully.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Key failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('toggle')
                    ->label(fn (AiApiKey $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (AiApiKey $record): string => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (AiApiKey $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (AiApiKey $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? 'Key activated' : 'Key deactivated')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
