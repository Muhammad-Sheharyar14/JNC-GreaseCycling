<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the live location of the driver.
     */
    public function driverLocation(): HasOne
    {
        return $this->hasOne(DriverLocation::class);
    }

    /**
     * Get the routes assigned to this user (driver).
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class, 'assigned_driver_id');
    }

    /**
     * Get the pickup events completed by this user (driver).
     */
    public function pickupEvents(): HasMany
    {
        return $this->hasMany(PickupEvent::class, 'driver_id');
    }

    /**
     * Get the route runs logged by this user (driver).
     */
    public function routeRuns(): HasMany
    {
        return $this->hasMany(RouteRun::class, 'driver_id');
    }

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active && $this->hasAnyRole(['Admin', 'Dispatcher', 'Accounting']);
    }
}
