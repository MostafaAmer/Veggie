<?php
declare(strict_types=1);

namespace App\Enums;

enum RefundMethod: string
{
    case OriginalMethod = 'original_method';
    case WalletCredit   = 'wallet_credit';
    case BankTransfer   = 'bank_transfer';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(
            fn(self $method) => $method->value,
            self::cases()
        );
    }

    public function label(): string
    {
        return match($this) {
            self::OriginalMethod => 'نفس طريقة الدفع الأصلية',
            self::WalletCredit   => 'رصيد المحفظة',
            self::BankTransfer   => 'تحويل بنكي',
        };
    }
}
