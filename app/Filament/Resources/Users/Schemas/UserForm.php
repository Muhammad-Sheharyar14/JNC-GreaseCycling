<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255)
                    ->placeholder(fn (string $context): string => $context === 'edit' ? 'Leave blank to keep current' : ''),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(50)
                    ->placeholder('Required for Drivers')
                    ->required(fn (callable $get) => in_array('Driver', (array) $get('roles'))),
                Toggle::make('active')
                    ->label('Active Status')
                    ->default(true),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->required(),
            ]);
    }
}
