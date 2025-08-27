<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CashOnDelivery = 'cash_on_delivery';
    case CreditCard = 'credit_card';
    case Wallet = 'wallet';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match($this) {
            self::CashOnDelivery => 'Cash on Delivery',
            self::CreditCard => 'Credit Card',
            self::Wallet => 'Wallet',
            self::BankTransfer => 'Bank Transfer',
        };
    }

    public static function values(): array
    {
        return array_map(
            fn(self $case) => $case->value,
            self::cases()
        );
    }
}