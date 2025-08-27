<?php

namespace App\Observers;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        event(new OrderCreated($order));
        if ($order->status) {
            event(new OrderStatusChanged($order, $order->status));
        }
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            event(new OrderStatusChanged($order, $order->status));
        }
    }
}