<?php

namespace App\Filament\Resources\Locations\Widgets;

use App\Models\Location;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LocationStatsWidget extends BaseWidget
{
    public ?Location $record = null;

    public function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $completedPickups = $this->record->pickupEvents()->where('status', 'completed')->get();
        $totalPounds = $completedPickups->sum('pounds_collected');
        $pickupsCount = $completedPickups->count();

        $averagePounds = $pickupsCount > 0 ? $totalPounds / $pickupsCount : 0;

        // Days since last completed pickup
        $lastPickup = $this->record->pickupEvents()
            ->where('status', 'completed')
            ->orderBy('occurred_at', 'desc')
            ->first();

        $daysSince = null;
        if ($lastPickup) {
            $daysSince = (int) abs(Carbon::parse($lastPickup->occurred_at)->diffInDays(now()));
        }

        return [
            Stat::make('Average Weight / Pickup', number_format($averagePounds, 2) . ' lbs')
                ->description('Pounds collected per service')
                ->color('success'),
            Stat::make('Time Since Last Service', $daysSince !== null ? $daysSince . ' days' : 'Never Serviced')
                ->description('Days elapsed since last collection')
                ->color('primary'),
            Stat::make('Total Pickups Completed', $pickupsCount)
                ->description('Total successful collections')
                ->color('info'),
        ];
    }
}
