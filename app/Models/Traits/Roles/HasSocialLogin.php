<?php

namespace App\Models\Traits;

trait HasSocialLogin
{
    public function getIsSocialLoginAttribute(): bool
    {
        return !is_null($this->provider);
    }

    public function scopeSocialLogin($query)
    {
        return $query->whereNotNull('provider');
    }

    public function validateForPassportPasswordGrant($password): bool
    {
        if ($this->is_social_login) {
            return false;
        }
        
        return parent::validateForPassportPasswordGrant($password);
    }
}
