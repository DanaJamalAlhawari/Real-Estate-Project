<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsVerification extends Model
{
    protected $table = 'sms_verifications';

    protected $guarded = ['*'];

    protected $hidden = [
        'otp_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_used' => 'boolean',
            'attempts' => 'integer',
        ];
    }
}