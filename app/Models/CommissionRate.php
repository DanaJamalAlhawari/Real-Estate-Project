<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRate extends Model
{
    protected $fillable = ['agent_id', 'percentage', 'active'];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(RealEstateUser::class, 'agent_id');
    }
}
