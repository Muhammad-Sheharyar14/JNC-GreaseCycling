<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_name',
        'phone',
        'email',
        'billing_address',
        'notes',
        'status',
        'contract_path',
    ];

    /**
     * Get the locations associated with the customer.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Get the payout records associated with the customer.
     */
    public function payoutRecords(): HasMany
    {
        return $this->hasMany(PayoutRecord::class);
    }

    /**
     * Get the pickup events associated with the customer's locations.
     */
    public function pickupEvents(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(PickupEvent::class, Location::class);
    }
}
