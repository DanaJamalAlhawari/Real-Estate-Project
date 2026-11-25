<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImage extends Model
{
    protected $table = 'property_images';

    protected $fillable = [
        'image_url',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function getPublicUrlAttribute(): string
    {
        $path = ltrim($this->image_url, '/');
        if (str_starts_with($path, 'assets/images/properties/Data/')) {
            $path = 'assets/properties/Data/'.substr($path, strlen('assets/images/properties/Data/'));
        }

        $publicPath = str_starts_with($path, 'assets/') || str_starts_with($path, 'storage/')
            ? $path
            : 'storage/'.$path;

        return asset(implode('/', array_map('rawurlencode', explode('/', $publicPath))));
    }

    public function isDemoDefaultImage(): bool
    {
        return (str_starts_with($this->image_url, 'assets/properties/Data/')
            || str_starts_with($this->image_url, 'assets/images/properties/Data/'))
            && strtolower(pathinfo($this->image_url, PATHINFO_FILENAME)) === 'default';
    }

    // العقار المرتبطة فيه الصورة
    public function property(): BelongsTo
    {
        return $this->belongsTo(
            Property::class,
            'property_id'
        );
    }

    // الغرفة المرتبطة فيها الصورة، إذا كانت صورة غرفة
    public function room(): BelongsTo
    {
        return $this->belongsTo(
            Room::class,
            'room_id'
        );
    }
}
