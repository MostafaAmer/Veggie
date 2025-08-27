<?php
namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}