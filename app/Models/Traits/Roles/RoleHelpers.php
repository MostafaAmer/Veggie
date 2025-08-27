<?php

namespace App\Models\Traits;

use App\Models\Role;

trait RoleHelpers
{
    public static function findByNameOrSlug(string $identifier): ?Role
    {
        return static::where('name', $identifier)
                     ->orWhere('slug', $identifier)
                     ->first();
    }
}