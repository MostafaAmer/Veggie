<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TransactionStatus extends Enum
{
    const Pending        = 'pending';
    const Completed      = 'completed';
    const Failed         = 'failed';
    const Cancelled      = 'cancelled';
    const RequiresAction = 'requires_action';
    const OnHold         = 'on_hold';
}
