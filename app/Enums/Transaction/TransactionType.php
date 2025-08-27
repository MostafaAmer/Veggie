<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TransactionType extends Enum
{
    const Payment        = 'payment';
    const Refund         = 'refund';
    const PartialRefund  = 'partial_refund';
    const Chargeback     = 'chargeback';
    const Adjustment     = 'adjustment';
    const Withdrawal     = 'withdrawal';
}
