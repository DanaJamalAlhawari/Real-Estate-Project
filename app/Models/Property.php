<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $table = 'properties';

    protected $fillable = [
        'agent_id',
        'title',
        'description',
        'category',
        'price',
        'city',
        'area',
        'address',
        'latitude',
        'longitude',
        'bedrooms',
        'bathrooms',
        'ownership_deed_url',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public const CATEGORIES = [
        'studio' => 'Studio',
        'student_apartment' => 'Student apartment',
        'apartment' => 'Apartment',
        'vacation' => 'Vacation',
    ];

    public function scopeDiscoverable($query)
    {
        return $query->where('verification_status', 'verified')
            ->whereIn('status', ['available', 'partially_rented']);
    }

    // الوكيل المسؤول عن العقار
    public function agent(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'agent_id'
        );
    }

    // المستخدم الذي راجع العقار
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'reviewed_by'
        );
    }

    // غرف العقار
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'property_id');
    }

    // صور العقار، بما فيها صور غرفه
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class, 'property_id');
    }

    // الخدمات المتوفرة في العقار
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'property_services',
            'property_id',
            'service_id'
        );
    }

    // حجوزات العقار، بما فيها حجوزات غرفه
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'property_id');
    }

    // المحادثات المرتبطة بالعقار
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'property_id');
    }

    // تقييمات العقار
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'property_id');
    }

    // المستخدمون الذين أضافوا العقار للمفضلة
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(
            RealEstateUser::class,
            'favorites',
            'property_id',
            'tenant_id'
        )->withPivot('created_at');
    }
}
