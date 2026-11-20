<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProfile extends Model
{
    protected $table = 'tenant_profiles';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'emergency_contact',
        'student_id',
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
