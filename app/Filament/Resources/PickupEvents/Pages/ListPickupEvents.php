<?php

namespace App\Filament\Resources\PickupEvents\Pages;

use App\Filament\Resources\PickupEvents\PickupEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPickupEvents extends ListRecords
{
    protected static string $resource = PickupEventResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
