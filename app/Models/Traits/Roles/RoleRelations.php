<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait RoleRelations
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'role_user')
                    ->withPivot('assigned_at', 'expires_at')
                    ->withTimestamps();
    }
}