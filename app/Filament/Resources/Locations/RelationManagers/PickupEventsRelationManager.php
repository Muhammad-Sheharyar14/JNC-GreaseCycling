<?php

namespace App\Filament\Resources\Locations\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;

class PickupEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'pickupEvents';

    protected static ?string $title = 'Collection History';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\DateTimePicker::make('occurred_at')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('pounds_collected')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(['completed' => 'Completed', 'skipped' => 'Skipped'])
                    ->required(),
                Forms\Components\TextInput::make('skip_reason')
                    ->nullable(),
                Forms\Components\Textarea::make('notes')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')
                    ->label('Date & Time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Driver')
                    ->sortable()
                    ->placeholder('System/Dispatcher'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'skipped' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pounds_collected')
                    ->label('Weight Collected')
                    ->numeric(2)
                    ->suffix(' lbs')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('skip_reason')
                    ->label('Skip Reason')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['completed' => 'Completed', 'skipped' => 'Skipped']),
            ])
            ->headerActions([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('exportBulkCsv')
                        ->label('Export Selected to CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $filename = 'pickups-export-' . now()->format('Y-md-His') . '.csv';
                            return response()->streamDownload(function () use ($records) {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['ID', 'Date & Time', 'Driver Name', 'Status', 'Pounds Collected', 'Skip Reason', 'Notes']);
                                foreach ($records as $record) {
                                    fputcsv($handle, [
                                        $record->id,
                                        $record->occurred_at?->toDateTimeString(),
                                        $record->driver?->name,
                                        $record->status,
                                        $record->pounds_collected,
                                        $record->skip_reason,
                                        $record->notes,
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
