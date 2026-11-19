<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAssistant extends Model
{
    protected $table = 'ai_assistant';

    protected $guarded = ['*'];

   
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'user_id'
        );
    }
}