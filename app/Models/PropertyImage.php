<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImage extends Model
{
    protected $table = 'property_images';

    protected $fillable = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
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