<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DriverLocation;

class DriverLocationController extends Controller
{
    /**
     * Update or create the authenticated driver's current coordinates.
     */
    public function update(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = $request->user();

        // Ensure only active drivers can post location updates
        if (!$user->hasRole('Driver')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $user->driverLocation()->updateOrCreate(
            [], // Match by relationship unique user_id
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully.',
        ]);
    }
}
