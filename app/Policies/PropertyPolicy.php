<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\RealEstateUser;

class PropertyPolicy
{
    public function update(RealEstateUser $user, Property $property): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'agent'
            && (int) $property->agent_id === (int) $user->id;
    }
}
