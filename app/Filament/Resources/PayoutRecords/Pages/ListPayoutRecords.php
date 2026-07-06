<?php

namespace App\Filament\Resources\PayoutRecords\Pages;

use App\Filament\Resources\PayoutRecords\PayoutRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayoutRecords extends ListRecords
{
    protected static string $resource = PayoutRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
