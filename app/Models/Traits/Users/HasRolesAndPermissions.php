<?php

namespace App\Models\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Role;
use App\Models\Notification;

trait HasRolesAndPermissions
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function getAllPermissions(): Collection
    {
        return Cache::remember("user.{$this->id}.permissions", 3600, function () {
            return $this->roles
                        ->flatMap->permissions
                        ->unique('id');
        });
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('admin');
    }

    public function getJWTClaims(): array
    {
        return [
            'uid' => $this->id,
            'rls' => $this->roles->pluck('slug')->toArray(),
            'iss' => config('app.url'),
            'vrf' => $this->is_verified,
            'act' => $this->is_active,
        ];
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
                    ->orderByDesc('created_at');
    }

    public function unreadNotifications(): MorphMany
    {
        return $this->notifications()
                    ->whereNull('read_at');
    }
}