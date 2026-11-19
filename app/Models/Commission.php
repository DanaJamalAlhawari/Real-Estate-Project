<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $table = 'commissions';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

   
    public function booking(): BelongsTo
    {
        return $this->belongsTo(
            Booking::class,
            'booking_id'
        );
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'agent_id'
        );
    }
}