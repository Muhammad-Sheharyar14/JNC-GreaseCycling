<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PickupEvent;
use App\Models\RouteRun;
use App\Models\ScheduledStop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverStopController extends Controller
{
    /**
     * Get details for a specific scheduled stop.
     */
    public function show(Request $request, ScheduledStop $scheduledStop)
    {
        $user = $request->user();

        // Verify driver is assigned to the stop's route
        if ((int) $scheduledStop->route->assigned_driver_id !== (int) $user->id) {
            return response()->json(['message' => 'Unauthorized stop access.'], 403);
        }

        return response()->json([
            'stop' => $scheduledStop->load('location.customer'),
        ]);
    }

    /**
     * Log a pickup event (completed or skipped) for a scheduled stop.
     */
    public function logPickup(Request $request, ScheduledStop $scheduledStop)
    {
        $user = $request->user();

        // Verify driver is assigned to the stop's route
        if ((int) $scheduledStop->route->assigned_driver_id !== (int) $user->id) {
            return response()->json(['message' => 'Unauthorized stop access.'], 403);
        }

        // Validate payload based on status
        $request->validate([
            'status' => ['required', Rule::in(['completed', 'skipped'])],
            'pounds_collected' => [
                Rule::requiredIf($request->status === 'completed'),
                'nullable',
                'numeric',
                'min:0',
            ],
            'notes' => ['nullable', 'string'],
            'skip_reason' => [
                Rule::requiredIf($request->status === 'skipped'),
                'nullable',
                'string',
                Rule::in(['closed', 'no_access', 'other']),
            ],
        ]);

        $today = Carbon::today()->toDateString();

        // 1. Unique-ish check: Warn if a pickup is logged twice for the same location on the same day
        $duplicateExists = PickupEvent::where('location_id', $scheduledStop->location_id)
            ->where('status', 'completed')
            ->whereDate('occurred_at', $today)
            ->exists();

        // 2. Create the PickupEvent
        $pickupEvent = PickupEvent::create([
            'location_id' => $scheduledStop->location_id,
            'route_id' => $scheduledStop->route_id,
            'driver_id' => $user->id,
            'occurred_at' => now(),
            'pounds_collected' => $request->status === 'completed' ? $request->pounds_collected : null,
            'notes' => $request->notes,
            'status' => $request->status,
            'skip_reason' => $request->status === 'skipped' ? $request->skip_reason : null,
        ]);

        // 3. Update ScheduledStop status & pickup_event link
        $scheduledStop->update([
            'status' => $request->status,
            'pickup_event_id' => $pickupEvent->id,
        ]);

        // 4. Automatically start route run if not already started
        $routeRun = RouteRun::where('route_id', $scheduledStop->route_id)
            ->whereDate('date', $today)
            ->first();

        if (!$routeRun || $routeRun->status === 'not_started') {
            RouteRun::updateOrCreate(
                ['route_id' => $scheduledStop->route_id, 'date' => $today],
                [
                    'driver_id' => $user->id,
                    'started_at' => now(),
                    'status' => 'in_progress',
                ]
            );
        }

        return response()->json([
            'message' => 'Pickup logged successfully.',
            'pickup_event' => $pickupEvent,
            'warning' => $duplicateExists ? 'A pickup has already been logged for this location today.' : null,
        ]);
    }
}
