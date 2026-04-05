<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pro' => 'success',
                        'starter' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('subscription_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success',
                        'trialing' => 'info',
                        'past_due' => 'warning',
                        'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('plan')
                    ->options([
                        'free' => 'Free',
                        'starter' => 'Starter',
                        'pro' => 'Pro',
                    ]),
                SelectFilter::make('subscription_status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'trialing' => 'Trialing',
                        'past_due' => 'Past Due',
                        'canceled' => 'Canceled',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
