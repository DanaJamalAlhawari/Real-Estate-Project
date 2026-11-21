<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class RealEstateUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'real_estate_users';

    protected $fillable = [
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }


    public function agentProfile(): HasOne
    {
        return $this->hasOne(AgentProfile::class, 'user_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $profile = $this->role === 'agent'
            ? $this->agentProfile
            : ($this->role === 'tenant' ? $this->tenantProfile : null);

        $name = trim(($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? ''));

        if ($name !== '') {
            return $name;
        }

        return $this->role === 'admin' ? 'Admin' : $this->email;
    }

    public function getDisplayInitialAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->display_name, 0, 1));
    }

    public function tenantProfile(): HasOne
    {
        return $this->hasOne(TenantProfile::class, 'user_id');
    }


    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'agent_id');
    }


    public function reviewedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'reviewed_by');
    }


    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }


    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tenant_id');
    }


    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'agent_id');
    }


    public function tenantConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'tenant_id');
    }


    public function agentConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'agent_id');
    }

    public function inboxConversations()
    {
        return match ($this->role) {
            'tenant' => Conversation::where('tenant_id', $this->id),
            'agent' => Conversation::where(function ($query) {
                $query->where('agent_id', $this->id)->orWhere(function ($support) {
                    $support->where('tenant_id', $this->id)->whereNull('property_id')
                        ->whereHas('agent', fn ($admin) => $admin->where('role', 'admin'));
                });
            }),
            'admin' => Conversation::where('agent_id', $this->id)->whereNull('property_id'),
        };
    }

    public function unreadMessageCount(): int
    {
        return Message::where('is_read', false)
            ->where('sender_id', '!=', $this->id)
            ->whereIn('conversation_id', $this->inboxConversations()->select('id'))
            ->count();
    }


    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }


    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tenant_id');
    }


    public function favoriteProperties(): BelongsToMany
    {
        return $this->belongsToMany(
            Property::class,
            'favorites',
            'tenant_id',
            'property_id'
        )->withPivot('created_at');
    }

    public function aiRequests(): HasMany
    {
        return $this->hasMany(AiAssistant::class, 'user_id');
    }
}
