<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

  
    public function property(): BelongsTo
    {
        return $this->belongsTo(
            Property::class,
            'property_id'
        );
    }

   
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'tenant_id'
        );
    }
}