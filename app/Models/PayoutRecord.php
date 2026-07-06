<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'date_range_start',
        'date_range_end',
        'total_pounds',
        'reimbursement_rate',
        'total_amount_owed',
        'is_paid',
        'paid_at',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'date_range_start' => 'date',
        'date_range_end' => 'date',
        'total_pounds' => 'decimal:2',
        'reimbursement_rate' => 'decimal:2',
        'total_amount_owed' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'date',
    ];

    /**
     * Get the customer associated with the payout record.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
