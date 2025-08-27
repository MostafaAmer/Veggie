<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

enum CouponScope: string
{
    case GLOBAL   = 'global';
    case CATEGORY = 'category';
    case PRODUCT  = 'product';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::GLOBAL   => 'عام (Global)',
            self::CATEGORY => 'خاص بفئة (Category)',
            self::PRODUCT  => 'خاص بمنتج (Product)',
        };
    }
}
