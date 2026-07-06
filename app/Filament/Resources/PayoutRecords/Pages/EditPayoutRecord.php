<?php

namespace App\Filament\Resources\PayoutRecords\Pages;

use App\Filament\Resources\PayoutRecords\PayoutRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPayoutRecord extends EditRecord
{
    protected static string $resource = PayoutRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
