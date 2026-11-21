<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentProfile extends Model
{
    protected $table = 'agent_profiles';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'agency_name',
        'license_number',
        'id_document_url',
    ];

    protected function casts(): array
    {
        return [
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'user_id'
        );
    }
}
