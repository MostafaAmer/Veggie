<?php
namespace App\Enums;

use BenSampo\Enum\Enum;

enum CouponType: string
{
    case PERCENTAGE   = 'percentage';
    case FIXED        = 'fixed';
    case FREE_SHIPPING= 'free_shipping';
    case BOGO         = 'bogo';

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
            self::PERCENTAGE    => 'نسبة مئوية (%)',
            self::FIXED         => 'مبلغ ثابت',
            self::FREE_SHIPPING => 'شحن مجاني',
            self::BOGO          => 'اشترٍ واحد واحصل على آخر',
        };
    }
}
