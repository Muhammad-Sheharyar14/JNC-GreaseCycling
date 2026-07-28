<?php

namespace App\Services;

use App\Models\Location;
use App\Models\PickupEvent;
use App\Models\Route;
use App\Models\ScheduledStop;
use Carbon\Carbon;
use DateTimeInterface;

class RouteScheduler
{
    /**
     * Generate scheduled stops for a given date.
     *
     * @param DateTimeInterface $date
     * @return int Number of stops generated
     */
    public function generateStopsForDate(DateTimeInterface $date): int
    {
        $carbonDate = Carbon::instance($date);
        $dateStr = $carbonDate->toDateString();

        // 1. Find all routes scheduled for this exact date of service
        $routes = Route::whereDate('date_of_service', $carbonDate)->get();
        $generatedCount = 0;

        foreach ($routes as $route) {
            // Check if we already have non-pending stops generated for this route/date
            // to avoid overwriting active driver progress
            $hasActiveStops = ScheduledStop::where('route_id', $route->id)
                ->whereDate('date', $carbonDate)
                ->where('status', '!=', 'pending')
                ->exists();

            if ($hasActiveStops) {
                continue; // Do not overwrite completed/skipped stops
            }

            // Clear any existing pending stops for this route on this date (idempotency)
            ScheduledStop::where('route_id', $route->id)
                ->whereDate('date', $carbonDate)
                ->where('status', 'pending')
                ->delete();

            // 2. Fetch active route stops templates in order
            $routeStops = $route->routeStops()
                ->where('is_active', true)
                ->orderBy('position', 'asc')
                ->get();

            $position = 1;
            foreach ($routeStops as $stop) {
                $location = $stop->location;

                // Skip if location is inactive, paused, or cancelled, or its customer is not active
                if (!$location || $location->status !== 'active' || !$location->customer || $location->customer->status !== 'active') {
                    continue;
                }

                // 3. Evaluate if the location is due for pickup today
                if ($this->isLocationDueForPickup($location, $carbonDate)) {
                    ScheduledStop::create([
                        'route_id' => $route->id,
                        'location_id' => $location->id,
                        'date' => $dateStr,
                        'position' => $position++,
                        'status' => 'pending',
                    ]);
                    $generatedCount++;
                }
            }
        }

        return $generatedCount;
    }

    /**
     * Check if a location is due for pickup on the given date based on its frequency and last pickup.
     *
     * @param Location $location
     * @param Carbon $date
     * @return bool
     */
    public function isLocationDueForPickup(Location $location, Carbon $date): bool
    {
        // On call locations are never scheduled automatically
        if ($location->service_frequency === 'on_call') {
            return false;
        }

        // Weekly locations are always scheduled when their route runs
        if ($location->service_frequency === 'weekly') {
            return true;
        }

        // Find the last completed pickup event for this location
        $lastPickup = PickupEvent::where('location_id', $location->id)
            ->where('status', 'completed')
            ->orderBy('occurred_at', 'desc')
            ->first();

        if (!$lastPickup) {
            // Never collected before, so it's due
            return true;
        }

        $lastPickupDate = Carbon::instance($lastPickup->occurred_at);
        $daysSinceLastPickup = (int) abs($date->diffInDays($lastPickupDate));

        if ($location->service_frequency === 'biweekly') {
            // Biweekly: due if 12 or more days have elapsed
            return $daysSinceLastPickup >= 12;
        }

        if ($location->service_frequency === 'monthly') {
            // Monthly: due if 26 or more days have elapsed
            return $daysSinceLastPickup >= 26;
        }

        return false;
    }
}
