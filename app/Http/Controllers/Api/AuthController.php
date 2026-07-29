<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate driver and return Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Ensure user is active
        if (!$user->active) {
            return response()->json(['message' => 'Your account is deactivated.'], 403);
        }

        // Validate role (Driver, Admin, Dispatcher, Accounting)
        $hasValidRole = $user->hasAnyRole(['Driver', 'Admin', 'Dispatcher', 'Accounting']);
        if (!$hasValidRole) {
            return response()->json(['message' => 'Access denied. Unauthorized role.'], 403);
        }

        $isAdminOrDispatcher = $user->hasAnyRole(['Admin', 'Dispatcher', 'Accounting']);

        if ($isAdminOrDispatcher) {
            // Log user into web session guard so Filament will recognize them as logged in
            \Illuminate\Support\Facades\Auth::guard('web')->login($user, true);
        }

        $token = $user->createToken('driver-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'redirect_to_admin' => $isAdminOrDispatcher,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Revoke driver Sanctum token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }
}
