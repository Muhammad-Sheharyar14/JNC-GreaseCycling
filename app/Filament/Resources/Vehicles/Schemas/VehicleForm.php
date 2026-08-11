<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Vehicle Name / Designation')
                    ->placeholder('e.g. Truck #101 - Isuzu NPR')
                    ->required(),
                TextInput::make('license_plate')
                    ->label('License Plate')
                    ->placeholder('e.g. TX-98765')
                    ->default(null),
                TextInput::make('make_model')
                    ->label('Make & Model')
                    ->placeholder('e.g. 2022 Freightliner M2')
                    ->default(null),
                TextInput::make('capacity_gallons')
                    ->label('Capacity (Gallons)')
                    ->numeric()
                    ->default(null),
                TextInput::make('capacity_lbs')
                    ->label('Capacity (Lbs)')
                    ->numeric()
                    ->default(null),
                Select::make('assigned_driver_id')
                    ->label('Assigned Driver')
                    ->relationship('assignedDriver', 'name', function ($query) {
                        return $query->whereHas('roles', function ($r) {
                            $r->where('name', 'Driver');
                        });
                    })
                    ->searchable()
                    ->preload()
                    ->default(null),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'maintenance' => 'Maintenance',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
