<?php

namespace App\Filament\Resources\Locations\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

use Filament\Tables\Filters\SelectFilter;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_address')
                    ->searchable(),
                TextColumn::make('service_frequency')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('reimbursement_rate')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('defaultRoute.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('service_frequency')
                    ->options([
                        'weekly' => 'Weekly',
                        'biweekly' => 'Biweekly',
                        'monthly' => 'Monthly',
                        'on_call' => 'On Call',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    
                    BulkAction::make('exportBulkCsv')
                        ->label('Export Selected to CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $filename = 'locations-export-' . now()->format('Y-md-His') . '.csv';
                            return response()->streamDownload(function () use ($records) {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['ID', 'Customer Name', 'Location Name', 'Service Address', 'Map Link', 'Special Instructions', 'Service Frequency', 'Reimbursement Rate', 'Status', 'Default Route Name']);
                                foreach ($records as $record) {
                                    fputcsv($handle, [
                                        $record->id,
                                        $record->customer?->name,
                                        $record->name,
                                        $record->service_address,
                                        $record->map_link,
                                        $record->special_instructions,
                                        $record->service_frequency,
                                        $record->reimbursement_rate,
                                        $record->status,
                                        $record->defaultRoute?->name,
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
