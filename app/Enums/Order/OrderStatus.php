<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case Processing = 'processing';
    case Shipped    = 'shipped';
    case Delivered  = 'delivered';
    case Cancelled  = 'cancelled';
    case Returned   = 'returned';
    case Refunded   = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::Confirmed  => 'Confirmed',
            self::Processing => 'Processing',
            self::Shipped    => 'Shipped',
            self::Delivered  => 'Delivered',
            self::Cancelled  => 'Cancelled',
            self::Returned   => 'Returned',
            self::Refunded   => 'Refunded',
        };
    }

    public static function dropdown(): array
    {
        return array_reduce(
            self::cases(),
            fn(array $carry, OrderStatus $status) => $carry + [$status->value => $status->label()],
            []
        );
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::Delivered, self::Cancelled,
            self::Returned,  self::Refunded,
        ], true);
    }
}