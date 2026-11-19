<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

   
    public function property(): BelongsTo
    {
        return $this->belongsTo(
            Property::class,
            'property_id'
        );
    }

    public function images(): HasMany
    {
        return $this->hasMany(
            PropertyImage::class,
            'room_id'
        );
    }

   
    public function bookings(): HasMany
    {
        return $this->hasMany(
            Booking::class,
            'room_id'
        );
    }
}