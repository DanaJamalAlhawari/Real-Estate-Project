<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'booking_id',
        'tenant_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // الحجز المرتبط بالدفعة
    public function booking(): BelongsTo
    {
        return $this->belongsTo(
            Booking::class,
            'booking_id'
        );
    }

    // المستأجر صاحب الدفعة
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'tenant_id'
        );
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }
}
