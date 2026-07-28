<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\RouteRun;
use App\Models\ScheduledStop;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DriverRouteController extends Controller
{
    /**
     * Get today's route run and scheduled stops for the driver.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        // 1. Get today's scheduled stops for routes assigned to the driver
        $stops = ScheduledStop::with(['location.customer'])
            ->whereHas('route', function ($query) use ($user) {
                $query->where('assigned_driver_id', $user->id);
            })
            ->whereDate('date', $today)
            ->orderBy('position', 'asc')
            ->get();

        // Find which route is active today (if any)
        $routeId = $stops->first()?->route_id;
        
        // If no stops are scheduled, check if there is a route assigned to the driver for today's date
        if (!$routeId) {
            $route = Route::where('assigned_driver_id', $user->id)
                ->whereDate('date_of_service', $today)
                ->first();
            $routeId = $route?->id;
        }

        // 2. Get today's route run details
        $routeRun = null;
        if ($routeId) {
            $routeRun = RouteRun::where('route_id', $routeId)
                ->whereDate('date', $today)
                ->first();
        }

        return response()->json([
            'route_id' => $routeId,
            'route_run' => $routeRun,
            'stops' => $stops,
        ]);
    }

    /**
     * Start today's route run.
     */
    public function start(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:routes,id',
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();
        $routeId = $request->route_id;

        // Verify driver is assigned to this route
        $route = Route::findOrFail($routeId);
        if ((int) $route->assigned_driver_id !== (int) $user->id) {
            return response()->json(['message' => 'You are not assigned to this route.'], 403);
        }

        $routeRun = RouteRun::updateOrCreate(
            ['route_id' => $routeId, 'date' => $today],
            [
                'driver_id' => $user->id,
                'started_at' => now(),
                'status' => 'in_progress',
            ]
        );

        return response()->json([
            'message' => 'Route run started.',
            'route_run' => $routeRun,
        ]);
    }

    /**
     * Mark today's route run as complete.
     */
    public function complete(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:routes,id',
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();
        $routeId = $request->route_id;

        $routeRun = RouteRun::where('route_id', $routeId)
            ->whereDate('date', $today)
            ->first();

        if (!$routeRun) {
            return response()->json(['message' => 'Route run not found for today.'], 404);
        }

        if ((int) $routeRun->driver_id !== (int) $user->id) {
            return response()->json(['message' => 'You are not authorized to complete this route.'], 403);
        }

        $routeRun->update([
            'ended_at' => now(),
            'status' => 'completed',
        ]);

        return response()->json([
            'message' => 'Route run marked as complete.',
            'route_run' => $routeRun,
        ]);
    }

    /**
     * Get the driver's profile details and history.
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        // 1. Calculate Lifetime stats
        $totalRoutesCompleted = RouteRun::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $totalStopsServiced = \App\Models\PickupEvent::where('driver_id', $user->id)
            ->count();

        $totalPoundsCollected = \App\Models\PickupEvent::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->sum('pounds_collected');

        // 2. Fetch recent pickup history (last 10 events) with location and customer details
        $recentPickups = \App\Models\PickupEvent::with(['location.customer'])
            ->where('driver_id', $user->id)
            ->orderBy('occurred_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'driver' => [
                'name' => $user->name,
                'email' => $user->email,
                'joined_at' => $user->created_at ? $user->created_at->toDateString() : now()->toDateString(),
            ],
            'stats' => [
                'total_routes_completed' => $totalRoutesCompleted,
                'total_stops_serviced' => $totalStopsServiced,
                'total_pounds_collected' => (float) $totalPoundsCollected,
            ],
            'recent_pickups' => $recentPickups,
        ]);
    }
}
