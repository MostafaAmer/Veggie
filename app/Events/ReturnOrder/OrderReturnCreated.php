<?php

namespace App\Events;

use App\Models\OrderReturn;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderReturnCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public OrderReturn $orderReturn) {}
}
