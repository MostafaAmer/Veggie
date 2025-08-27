<?php
declare(strict_types=1);

namespace App\Enums;

enum ReturnItemCondition: string
{
    case Unopened = 'unopened';
    case Opened   = 'opened';
    case Damaged  = 'damaged';

    public function label(): string
    {
        return match($this) {
            self::Unopened => 'Unopened',
            self::Opened   => 'Opened',
            self::Damaged  => 'Damaged',
        };
    }
}
