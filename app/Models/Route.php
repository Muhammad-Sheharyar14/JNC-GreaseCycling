<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_days',
        'assigned_driver_id',
    ];

    protected $casts = [
        'service_days' => 'array',
    ];

    /**
     * Get the driver assigned to this route.
     */
    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    /**
     * Get the stops associated with this route.
     */
    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    /**
     * Get the pickup events associated with this route.
     */
    public function pickupEvents(): HasMany
    {
        return $this->hasMany(PickupEvent::class);
    }

    /**
     * Get the scheduled stops associated with this route.
     */
    public function scheduledStops(): HasMany
    {
        return $this->hasMany(ScheduledStop::class);
    }

    /**
     * Get the runs logged for this route.
     */
    public function routeRuns(): HasMany
    {
        return $this->hasMany(RouteRun::class);
    }
}
