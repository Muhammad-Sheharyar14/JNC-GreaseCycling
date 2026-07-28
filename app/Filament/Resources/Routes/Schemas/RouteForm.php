<?php

namespace App\Filament\Resources\Routes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class RouteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                DatePicker::make('date_of_service')
                    ->label('Date of Service')
                    ->required(),
                Select::make('assigned_driver_id')
                    ->label('Assigned Driver')
                    ->relationship(
                        name: 'assignedDriver',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'Driver'))
                    )
                    ->searchable()
                    ->preload()
                    ->default(null),
            ]);
    }
}
