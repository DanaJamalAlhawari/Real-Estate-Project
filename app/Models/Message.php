<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'body',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    // المحادثة التابعة إلها الرسالة
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(
            Conversation::class,
            'conversation_id'
        );
    }

    // المستخدم الذي أرسل الرسالة
    public function sender(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'sender_id'
        );
    }
}