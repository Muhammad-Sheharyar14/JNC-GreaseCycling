<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'location_id',
        'date',
        'position',
        'status',
        'pickup_event_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the route this scheduled stop belongs to.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the location for this scheduled stop.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the pickup event associated with this scheduled stop.
     */
    public function pickupEvent(): BelongsTo
    {
        return $this->belongsTo(PickupEvent::class);
    }
}
