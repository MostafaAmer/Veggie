<?php

namespace App\Enums;

enum OrderItemStatus: string
{
    case Pending            = 'pending';
    case Confirmed          = 'confirmed';
    case Shipped            = 'shipped';
    case Delivered          = 'delivered';
    case Returned           = 'returned';
    case Refunded           = 'refunded';
    case PartiallyRefunded  = 'partially_refunded';

    public function label(): string { 
        return match($this) {
            self::Pending           => 'Pending',
            self::Confirmed         => 'Confirmed',
            self::Shipped           => 'Shipped',
            self::Delivered         => 'Delivered',
            self::Returned          => 'Returned',
            self::Refunded          => 'Refunded',
            self::PartiallyRefunded => 'Partially Refunded',
        };
    }

    public static function returnable(): array
    {
        return [
            self::Returned,
            self::Refunded,
            self::PartiallyRefunded,
        ];
    }

    public function isReturnable(): bool
    {
        return in_array($this, self::returnable(), true);
    }

    public static function dropdown(): array
    {
        return array_reduce(
            self::cases(),
            fn(array $carry, OrderItemStatus $status) => $carry + [$status->value => $status->label()],
            []
        );
    }
}
