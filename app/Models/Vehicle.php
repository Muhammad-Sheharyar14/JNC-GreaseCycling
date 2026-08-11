<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'license_plate',
        'make_model',
        'capacity_gallons',
        'capacity_lbs',
        'status',
        'assigned_driver_id',
        'notes',
    ];

    protected $casts = [
        'capacity_gallons' => 'decimal:2',
        'capacity_lbs' => 'decimal:2',
    ];

    /**
     * Get the driver assigned to this vehicle.
     */
    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    /**
     * Get the routes assigned to this vehicle.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }
}
