<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PickupEvent;
use App\Models\Route;
use App\Models\RouteRun;
use App\Models\RouteStop;
use App\Models\ScheduledStop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DriverStopController extends Controller
{
    /**
     * Helper to resolve a ScheduledStop from an ID (ScheduledStop ID, RouteStop ID, or Location ID fallback).
     */
    private function resolveScheduledStop(Request $request, $id): ?ScheduledStop
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        // 1. Direct ScheduledStop lookup by primary key
        $scheduledStop = ScheduledStop::find($id);
        if ($scheduledStop) {
            return $scheduledStop;
        }

        // 2. Check if $id is a RouteStop ID (from route_stops table)
        $routeStop = RouteStop::find($id);
        if ($routeStop) {
            $scheduledStop = ScheduledStop::where('route_id', $routeStop->route_id)
                ->where('location_id', $routeStop->location_id)
                ->first();

            if ($scheduledStop) {
                return $scheduledStop;
            }

            return ScheduledStop::create([
                'route_id' => $routeStop->route_id,
                'location_id' => $routeStop->location_id,
                'date' => $today,
                'position' => $routeStop->position ?? 1,
                'status' => 'pending',
            ]);
        }

        // 3. Search ScheduledStop by location_id on driver's routes
        $scheduledStop = ScheduledStop::whereHas('route', function ($query) use ($user) {
                $query->where('assigned_driver_id', $user->id);
            })
            ->where('location_id', $id)
            ->first();

        if ($scheduledStop) {
            return $scheduledStop;
        }

        // 4. Search any scheduled stop matching location_id
        $scheduledStop = ScheduledStop::where('location_id', $id)->latest()->first();
        if ($scheduledStop) {
            return $scheduledStop;
        }

        // 5. Fallback: If $id is a valid Location ID, auto-create route & scheduled stop
        $location = Location::find($id);
        if ($location) {
            // Find or create driver's route for today
            $route = Route::where('assigned_driver_id', $user->id)
                ->whereDate('date_of_service', $today)
                ->first();

            if (!$route) {
                $route = Route::where('assigned_driver_id', $user->id)->latest()->first();
            }

            if (!$route) {
                $route = Route::create([
                    'name' => 'Driver Route - ' . Carbon::today()->format('M d, Y'),
                    'date_of_service' => $today,
                    'assigned_driver_id' => $user->id,
                ]);
            }

            $maxPos = ScheduledStop::where('route_id', $route->id)->max('position') ?? 0;
            return ScheduledStop::create([
                'route_id' => $route->id,
                'location_id' => $location->id,
                'date' => $today,
                'position' => $maxPos + 1,
                'status' => 'pending',
            ]);
        }

        return null;
    }

    /**
     * Helper to save base64 image data to public storage.
     */
    private function saveBase64Image(?string $base64Data, string $folder): ?string
    {
        if (!$base64Data || !preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            return null;
        }

        $imageData = substr($base64Data, strpos($base64Data, ',') + 1);
        $imageData = base64_decode($imageData);

        if ($imageData === false) {
            return null;
        }

        $filename = $folder . '/' . Str::random(40) . '.png';
        Storage::disk('public')->put($filename, $imageData);

        return $filename;
    }

    /**
     * Get details for a specific scheduled stop.
     */
    public function show(Request $request, $id)
    {
        $scheduledStop = $this->resolveScheduledStop($request, $id);

        if (!$scheduledStop) {
            return response()->json(['message' => 'Scheduled stop not found. Please refresh your route.'], 404);
        }

        $user = $request->user();

        // Ensure driver assignment
        if ($scheduledStop->route && (!$scheduledStop->route->assigned_driver_id || (int) $scheduledStop->route->assigned_driver_id !== (int) $user->id)) {
            $scheduledStop->route->update(['assigned_driver_id' => $user->id]);
        }

        return response()->json([
            'stop' => $scheduledStop->load('location.customer'),
        ]);
    }

    /**
     * Log a pickup event (completed or skipped) for a scheduled stop.
     */
    public function logPickup(Request $request, $id)
    {
        $scheduledStop = $this->resolveScheduledStop($request, $id);

        if (!$scheduledStop) {
            return response()->json(['message' => 'Scheduled stop not found. Please refresh your route.'], 404);
        }

        $user = $request->user();

        // Ensure driver assignment
        if ($scheduledStop->route && (!$scheduledStop->route->assigned_driver_id || (int) $scheduledStop->route->assigned_driver_id !== (int) $user->id)) {
            $scheduledStop->route->update(['assigned_driver_id' => $user->id]);
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
            'material_type' => ['nullable', 'string'],
            'unit' => ['nullable', 'string'],
            'gross_weight' => ['nullable', 'numeric'],
            'tare_weight' => ['nullable', 'numeric'],
            'net_weight' => ['nullable', 'numeric'],
            'container_count' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'skip_reason' => [
                Rule::requiredIf($request->status === 'skipped'),
                'nullable',
                'string',
                Rule::in(['closed', 'no_access', 'other']),
            ],
            'driver_signature' => ['nullable', 'string'],
            'customer_signature' => ['nullable', 'string'],
            'photos' => ['nullable', 'array'],
        ]);

        $today = Carbon::today()->toDateString();

        // Process Base64 Signatures
        $driverSigPath = $this->saveBase64Image($request->driver_signature, 'signatures/driver');
        $customerSigPath = $this->saveBase64Image($request->customer_signature, 'signatures/customer');

        // Process Photos array (base64 or URLs)
        $photoPaths = [];
        if ($request->has('photos') && is_array($request->photos)) {
            foreach ($request->photos as $photoData) {
                if (is_string($photoData) && str_starts_with($photoData, 'data:image')) {
                    $savedPath = $this->saveBase64Image($photoData, 'pickup-photos');
                    if ($savedPath) {
                        $photoPaths[] = $savedPath;
                    }
                } elseif (is_string($photoData)) {
                    $photoPaths[] = $photoData;
                }
            }
        }

        // Calculate net weight if gross and tare are provided
        $poundsCollected = $request->status === 'completed'
            ? ($request->pounds_collected ?? $request->net_weight ?? (($request->gross_weight && $request->tare_weight) ? ($request->gross_weight - $request->tare_weight) : null))
            : null;

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
            'pounds_collected' => $poundsCollected,
            'material_type' => $request->material_type ?? 'used_cooking_oil',
            'unit' => $request->unit ?? 'lbs',
            'gross_weight' => $request->gross_weight,
            'tare_weight' => $request->tare_weight,
            'net_weight' => $request->net_weight ?? $poundsCollected,
            'container_count' => $request->container_count ?? 1,
            'driver_signature_path' => $driverSigPath,
            'customer_signature_path' => $customerSigPath,
            'photos' => $photoPaths,
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
