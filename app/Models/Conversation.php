<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $table = 'conversations';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(
            Property::class,
            'property_id'
        )->withTrashed();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'tenant_id'
        );
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(
            RealEstateUser::class,
            'agent_id'
        );
    }

    public function messages(): HasMany
    {
        return $this->hasMany(
            Message::class,
            'conversation_id'
        );
    }

    public function hasParticipant(RealEstateUser $user): bool
    {
        return in_array((int) $user->getKey(), [
            (int) $this->tenant_id,
            (int) $this->agent_id,
        ], true);
    }
}
