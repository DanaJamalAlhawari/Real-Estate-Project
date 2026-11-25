<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyDailyView extends Model
{
    public $timestamps = false;

    protected $fillable = ['property_id', 'viewed_on', 'views'];
}
