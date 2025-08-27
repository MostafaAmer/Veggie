<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class OrderNotificationType extends Enum
{
    const Placed     = 'order_placed';
    const Processing = 'order_processing';
    const Shipped    = 'order_shipped';
    const Delivered  = 'order_delivered';
    const Cancelled  = 'order_cancelled';
    const AdminAlert = 'admin_order_placed';
}
