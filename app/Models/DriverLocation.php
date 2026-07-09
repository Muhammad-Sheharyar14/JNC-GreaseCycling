<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
    ];

    /**
     * Get the user (driver) associated with the location.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
