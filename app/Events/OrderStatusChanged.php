<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use SerializesModels;

    public function __construct(
        public Order   $order,
        public string  $status,
        public ?string $notes = null
    ) {}
}
