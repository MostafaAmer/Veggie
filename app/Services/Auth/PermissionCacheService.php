<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class PermissionCacheService
{
    public static function getUserPermissions(User $user): Collection
    {
        $key = "user.{$user->id}.permissions";
        return Cache::remember(
            $key,
            now()->addHour(),
            fn() => $user->roles
                ->flatMap(fn($role) => $role->permissions)
                ->unique('id')
                ->values()
        );

    }

    public static function clearUserPermissions(User $user): void
    {
        Cache::forget("user.{$user->id}.permissions");
    }
}