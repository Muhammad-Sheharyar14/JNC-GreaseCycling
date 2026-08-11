<?php

namespace App\Filament\Resources\Vehicles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Vehicle Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('license_plate')
                    ->label('License Plate')
                    ->searchable(),
                TextColumn::make('make_model')
                    ->label('Make / Model')
                    ->searchable(),
                TextColumn::make('capacity_gallons')
                    ->label('Cap (Gal)')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('capacity_lbs')
                    ->label('Cap (Lbs)')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('assignedDriver.name')
                    ->label('Assigned Driver')
                    ->placeholder('Unassigned')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'maintenance' => 'warning',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'maintenance' => 'Maintenance',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
