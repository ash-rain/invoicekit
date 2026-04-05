<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),
                TextColumn::make('admin.name')
                    ->label('Author')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Draft'),
            ])
            ->filters([
                Filter::make('published')
                    ->label('Published')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('published_at')->where('published_at', '<=', now())),
                Filter::make('draft')
                    ->label('Draft')
                    ->query(fn (Builder $query): Builder => $query->whereNull('published_at')->orWhere('published_at', '>', now())),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
