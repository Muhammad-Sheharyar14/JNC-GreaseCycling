<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Container extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'container_type',
        'capacity',
        'date_placed',
        'date_removed',
    ];

    protected $casts = [
        'date_placed' => 'date',
        'date_removed' => 'date',
    ];

    /**
     * Get the location associated with the container.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
