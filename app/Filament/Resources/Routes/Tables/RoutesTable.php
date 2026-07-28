<?php

namespace App\Filament\Resources\Routes\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoutesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_of_service')
                    ->label('Date of Service')
                    ->date()
                    ->sortable(),
                TextColumn::make('assignedDriver.name')
                    ->label('Assigned Driver')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('exportBulkCsv')
                        ->label('Export Selected to CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $filename = 'routes-export-' . now()->format('Y-md-His') . '.csv';
                            return response()->streamDownload(function () use ($records) {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['ID', 'Route Name', 'Date of Service', 'Assigned Driver Name']);
                                foreach ($records as $record) {
                                    fputcsv($handle, [
                                        $record->id,
                                        $record->name,
                                        $record->date_of_service ? $record->date_of_service->format('Y-m-d') : '',
                                        $record->assignedDriver?->name,
                                    ]);
                                }
                                fclose($handle);
                            }, $filename, [
                                'Content-Type' => 'text/csv',
                                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                            ]);
                        }),
                ]),
            ]);
    }
}
