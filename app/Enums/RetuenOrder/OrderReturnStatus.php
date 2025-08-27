<?php
declare(strict_types=1);

namespace App\Enums;

enum OrderReturnStatus: string
{
    case Pending             = 'pending';
    case Approved            = 'approved';
    case Rejected            = 'rejected';
    case Refunded            = 'refunded';
    case PartiallyRefunded   = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending           => 'Pending',
            self::Approved          => 'Approved',
            self::Rejected          => 'Rejected',
            self::Refunded          => 'Refunded',
            self::PartiallyRefunded => 'Partially Refunded',
        };
    }
}
