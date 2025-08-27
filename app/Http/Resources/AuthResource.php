<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone'        => $this->whenNotNull($this->phone),
            'provider'     => $this->whenNotNull($this->provider),
            'avatar_url'   => $this->avatar_url,
            'meta'         => [
                'is_verified'      => (bool) $this->is_verified,
                'is_active'        => (bool) $this->is_active,
                'is_social_login'  => (bool) $this->is_social_login,
                'last_login'       => $this->last_login_at?->toIso8601String(),
            ],
            'roles'        => $this->whenLoaded('roles', fn() => $this->roles->map(fn($role) => [
                'id'    => $role->id,
                'name'  => $role->name,
                'slug'  => $role->slug,
                'level' => $role->level,
            ])),
            'permissions'  => $this->whenLoaded('permissions', fn() => $this->getAllPermissions()),
            'links'        => [
                'self'   => route('api.v1.profile'),
                'logout' => route('api.v1.logout'),
            ],
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'auth_version'     => config('auth.version', '1.0'),
                'token_expires_in' => config('auth.token_expiration_seconds', 30 * 24 * 60 * 60),
            ],
        ];
    }

}