<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'service_address',
        'map_link',
        'special_instructions',
        'service_frequency',
        'reimbursement_rate',
        'status',
        'default_route_id',
    ];

    protected $casts = [
        'reimbursement_rate' => 'decimal:2',
    ];

    /**
     * Get the customer that owns the location.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the default route assigned to this location.
     */
    public function defaultRoute(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'default_route_id');
    }

    /**
     * Get the containers placed at this location.
     */
    public function containers(): HasMany
    {
        return $this->hasMany(Container::class);
    }

    /**
     * Get the route stops referencing this location.
     */
    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    /**
     * Get the pickup events logged for this location.
     */
    public function pickupEvents(): HasMany
    {
        return $this->hasMany(PickupEvent::class);
    }

    /**
     * Get the scheduled stops logged for this location.
     */
    public function scheduledStops(): HasMany
    {
        return $this->hasMany(ScheduledStop::class);
    }
}
