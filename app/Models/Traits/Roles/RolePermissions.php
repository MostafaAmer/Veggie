<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Cache;

trait RolePermissions
{
    public function getPermissionsAttribute(): array
    {
        return Cache::remember("role.{$this->id}.permissions", 3600, function () {
            return json_decode($this->attributes['permissions'] ?? '[]', true);
        });
    }
    
    public function setPermissionsAttribute(array $permissions): void
    {
        $this->attributes['permissions'] = json_encode(array_values($permissions));
        Cache::forget("role.{$this->id}.permissions");
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function givePermission(string $permission): self
    {
        $perms = $this->permissions;
        if (! in_array($permission, $perms, true)) {
            $perms[] = $permission;
            $this->permissions = $perms;
            $this->save();
        }

        return $this;
    }

    public function revokePermission(string $permission): self
    {
        $this->permissions = array_diff($this->permissions, [$permission]);
        $this->save();

        return $this;
    }

    public function syncPermissions(array $permissions): self
    {
        $this->permissions = $permissions;
        $this->save();

        return $this;
    }
}