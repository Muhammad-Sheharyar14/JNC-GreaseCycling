<?php

namespace App\Filament\Resources\PickupEvents\Pages;

use App\Filament\Resources\PickupEvents\PickupEventResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPickupEvent extends ViewRecord
{
    protected static string $resource = PickupEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
        ];
    }
}
