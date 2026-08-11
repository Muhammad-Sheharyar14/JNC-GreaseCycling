<?php

namespace App\Filament\Resources\PickupEvents;

use App\Filament\Resources\PickupEvents\Pages\ListPickupEvents;
use App\Filament\Resources\PickupEvents\Pages\ViewPickupEvent;
use App\Filament\Resources\PickupEvents\Schemas\PickupEventForm;
use App\Filament\Resources\PickupEvents\Tables\PickupEventsTable;
use App\Models\PickupEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PickupEventResource extends Resource
{
    protected static ?string $model = PickupEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Pickup Logs';

    protected static ?string $pluralModelLabel = 'Pickup Logs & Manifests';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return PickupEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PickupEventsTable::configure($table);
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
            'index' => ListPickupEvents::route('/'),
            'view' => ViewPickupEvent::route('/{record}'),
        ];
    }
}
