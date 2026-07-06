<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickupEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'route_id',
        'driver_id',
        'occurred_at',
        'pounds_collected',
        'notes',
        'status',
        'skip_reason',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'pounds_collected' => 'decimal:2',
    ];

    /**
     * Get the location for this pickup event.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the route this pickup event was logged under.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the driver (user) who completed this pickup event.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
