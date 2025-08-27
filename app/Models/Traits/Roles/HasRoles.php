<?php

namespace App\Models\Traits;

use App\Models\Role;
use App\Services\PermissionCacheService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
                    ->withPivot('assigned_at', 'expires_at')
                    ->withTimestamps();
    }

    public function assignRole($role): void
    {
        if (is_string($role)) {
            $role = Role::findByNameOrSlug($role);
        }

        $this->roles()->syncWithoutDetaching([
            $role->id => ['assigned_at' => now()]
        ]);

        PermissionCacheService::clearUserPermissions($this);
    }

    public function revokeRole($role): void
    {
        if (is_string($role)) {
            $role = Role::findByNameOrSlug($role);
        }

        $this->roles()->detach($role->id);
        PermissionCacheService::clearUserPermissions($this);
    }

    public function syncRoles(array $roles): void
    {
        $ids = array_map(fn($r) => is_string($r)
            ? Role::findByNameOrSlug($r)->id
            : $r->id,
        $roles);

        $this->roles()->sync($ids);
        PermissionCacheService::clearUserPermissions($this);
    }

    public function hasRole(string $identifier): bool
    {
        return $this->roles()
                    ->where(fn($q) => $q->where('name', $identifier)
                                        ->orWhere('slug', $identifier))
                    ->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return PermissionCacheService::getUserPermissions($this)
                                     ->contains($permission);
    }

    /**
     * Scope to filter by a single role.
     */
    public function scopeWithRole($q, string $role)
    {
        return $q->whereHas('roles', fn($q2) => $q2
                    ->where('name', $role)
                    ->orWhere('slug', $role));
    }
}