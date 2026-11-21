<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $table = 'commissions';

    protected $fillable = [
        'booking_id',
        'payment_id',
        'agent_id',
        'commission_rate',
        'commission_amount',
        'agent_net_amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'agent_net_amount' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'agent_id'
        );
    }
}
