<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Subscription')
                    ->columns(2)
                    ->schema([
                        Select::make('plan')
                            ->options([
                                'free' => 'Free',
                                'starter' => 'Starter',
                                'pro' => 'Pro',
                            ])
                            ->required(),
                        Select::make('subscription_status')
                            ->options([
                                'active' => 'Active',
                                'trialing' => 'Trialing',
                                'past_due' => 'Past Due',
                                'canceled' => 'Canceled',
                                'incomplete' => 'Incomplete',
                            ])
                            ->nullable(),
                        DateTimePicker::make('trial_ends_at')
                            ->label('Trial Ends At')
                            ->nullable(),
                        DateTimePicker::make('subscribed_until')
                            ->label('Subscribed Until')
                            ->nullable(),
                    ]),
            ]);
    }
}
