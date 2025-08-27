<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Hash;

trait UserAccessors
{
    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return asset("storage/avatars/{$this->avatar}");
    }

    public function getIsSocialLoginAttribute(): bool
    {
        return $this->provider !== null && $this->provider_id !== null;
    }

    public function setPasswordAttribute(string $value): void
    {
        if ($value === '') {
            return;
        }

        $this->attributes['password'] = Hash::needsRehash($value)
            ? bcrypt($value)
            : $value;
    }
}