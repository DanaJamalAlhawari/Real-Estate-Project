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