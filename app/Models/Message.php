<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }
    public function chatPayload(): array
    {
        $original = $this->replyTo;

        return [
            'id' => $this->id,
            'body' => $this->body,
            'sender_id' => $this->sender_id,
            'reply_to' => $original ? [
                'id' => $original->id,
                'body' => $original->body,
            ] : null,
        ];
    }
    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }
}
