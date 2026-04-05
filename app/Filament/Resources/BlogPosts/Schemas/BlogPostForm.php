<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create'
                                ? $set('slug', Str::slug($state))
                                : null)
                            ->columnSpanFull(),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        RichEditor::make('body')
                            ->required()
                            ->fileAttachmentsDisk('s3')
                            ->fileAttachmentsDirectory('blog-images')
                            ->columnSpanFull(),
                        FileUpload::make('featured_image')
                            ->label('Featured Image')
                            ->image()
                            ->disk('s3')
                            ->directory('blog-featured')
                            ->visibility('public')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                Section::make('SEO')
                    ->columns(1)
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(255)
                            ->nullable(),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->maxLength(320)
                            ->nullable()
                            ->rows(3),
                    ]),
                Section::make('Publishing')
                    ->schema([
                        DateTimePicker::make('published_at')
                            ->label('Publish At')
                            ->nullable()
                            ->helperText('Leave blank to keep as draft.'),
                    ]),
            ]);
    }
}
