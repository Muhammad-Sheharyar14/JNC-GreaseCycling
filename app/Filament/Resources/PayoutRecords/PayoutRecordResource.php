<?php

namespace App\Filament\Resources\PayoutRecords;

use App\Filament\Resources\PayoutRecords\Pages\CreatePayoutRecord;
use App\Filament\Resources\PayoutRecords\Pages\EditPayoutRecord;
use App\Filament\Resources\PayoutRecords\Pages\ListPayoutRecords;
use App\Filament\Resources\PayoutRecords\Schemas\PayoutRecordForm;
use App\Filament\Resources\PayoutRecords\Tables\PayoutRecordsTable;
use App\Models\PayoutRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PayoutRecordResource extends Resource
{
    protected static ?string $model = PayoutRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PayoutRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayoutRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayoutRecords::route('/'),
            'create' => CreatePayoutRecord::route('/create'),
            'edit' => EditPayoutRecord::route('/{record}/edit'),
        ];
    }
}
