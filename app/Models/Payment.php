<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    protected $guarded = ['*'];

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
}