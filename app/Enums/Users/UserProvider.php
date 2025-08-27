<?php
namespace App\Enums;

enum UserProvider: string
{
    case GOOGLE = 'google';
    case APPLE = 'apple';
    
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}