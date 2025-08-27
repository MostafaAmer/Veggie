<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait RoleScopes
{
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeByLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }
}