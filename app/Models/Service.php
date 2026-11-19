<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $table = 'services';

    protected $fillable = [
        'name',
        'icon_name',
    ];

    
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(
            Property::class,
            'property_services',
            'service_id',
            'property_id'
        );
    }
}