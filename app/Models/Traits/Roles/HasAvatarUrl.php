<?php

namespace App\Models\Traits;

trait HasAvatarUrl
{
    public function getAvatarUrlAttribute(): string
    {
        if (empty($this->avatar)) {
            return asset('images/default-avatar.png');
        }

        return str_starts_with($this->avatar, 'http')
            ? $this->avatar
            : asset("storage/{$this->avatar}");
    }
}