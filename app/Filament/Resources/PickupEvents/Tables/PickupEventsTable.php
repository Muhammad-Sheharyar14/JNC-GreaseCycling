<?php

namespace App\Filament\Resources\PickupEvents\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PickupEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                
                // TextColumn::make('location.customer.name')
                //     ->label('Customer')
                //     ->searchable()
                //     ->sortable(),
                TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('material_type')
                    ->label('Material')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper(str_replace('_', ' ', $state ?? 'UCO')))
                    ->color(fn ($state) => match ($state) {
                        'used_cooking_oil' => 'success',
                        'grease' => 'warning',
                        'tallow' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('pounds_collected')
                    ->label('Pounds (lbs)')
                    ->numeric(2)
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'skipped' => 'danger',
                        default => 'gray',
                    }),
                // TextColumn::make('photos')
                //     ->label('Photos')
                //     ->badge()
                //     ->color(fn ($record) => !empty($record->photos) && is_array($record->photos) && count($record->photos) > 0 ? 'info' : 'gray')
                //     ->formatStateUsing(function ($record) {
                //         if (!empty($record->photos) && is_array($record->photos) && count($record->photos) > 0) {
                //             $count = count($record->photos);
                //             return "📷 {$count} " . ($count === 1 ? 'Photo' : 'Photos');
                //         }
                //         return '—';
                //     }),
                // TextColumn::make('driver_signature_path')
                //     ->label('Signatures')
                //     ->formatStateUsing(function ($record) {
                //         $hasDriver = !empty($record->driver_signature_path);
                //         $hasCustomer = !empty($record->customer_signature_path);
                //         if ($hasDriver && $hasCustomer) return '✍️ Driver & Customer';
                //         if ($hasDriver) return '✍️ Driver Only';
                //         if ($hasCustomer) return '✍️ Customer Only';
                //         return '—';
                //     }),
                TextColumn::make('occurred_at')
                    ->label('Date & Time')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'skipped' => 'Skipped',
                    ]),
                SelectFilter::make('material_type')
                    ->options([
                        'used_cooking_oil' => 'Used Cooking Oil',
                        'grease' => 'Yellow / Brown Grease',
                        'tallow' => 'Tallow',
                        'other' => 'Other / Feedstocks',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('printManifest')
                        ->label('Print')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn ($record) => route('manifest.show', $record->id))
                        ->openUrlInNewTab(),
                    Action::make('downloadPdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('warning')
                        ->url(fn ($record) => route('manifest.download', $record->id))
                        ->openUrlInNewTab(),
                    ViewAction::make(),
                ]),
            ]);
    }
}
