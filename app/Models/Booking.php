<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'start_date',
        'duration',
        'duration_unit',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'duration' => 'integer',
            'total_price' => 'decimal:2',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(
            Property::class,
            'property_id'
        )->withTrashed();
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(
            Room::class,
            'room_id'
        );
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'tenant_id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class,
            'booking_id'
        );
    }

    public function scopeReserved($query)
    {
        return $query->whereIn('status', ['confirmed', 'completed']);
    }

    public function scopeOverlapping($query, $startDate, $endDate)
    {
        return $query->whereDate('start_date', '<', $endDate)
            ->whereDate('end_date', '>', $startDate);
    }

    // العمولة المرتبطة بالحجز
    public function commission(): HasOne
    {
        return $this->hasOne(
            Commission::class,
            'booking_id'
        );
    }
}
