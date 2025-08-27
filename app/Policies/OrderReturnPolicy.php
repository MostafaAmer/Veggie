<?php

namespace App\Policies;

use App\Models\OrderReturn;
use App\Models\User;
use App\Models\Order;
use App\Enums\OrderReturnStatus;


class OrderReturnPolicy
{
    public function create(User $user, Order $order): bool
    {
        return $order->user_id === $user->id && 
               $order->canReturn() &&
               $order->delivered_at->diffInDays(now()) <= config('orders.return_days', 14);
    }

    public function viewAny(User $user): bool
    {
        return $user->can('manage_orders');
    }

    public function view(User $user, OrderReturn $return): bool
    {
        return $return->user_id === $user->id || 
               $user->can('manage_orders');
    }

    public function approve(User $user, OrderReturn $return): bool
    {
        return $user->can('manage_orders') && 
               $return->status === OrderReturnStatus::Pending;
    }

    public function reject(User $user, OrderReturn $return): bool
    {
        return $user->can('manage_orders') && 
               $return->status === OrderReturnStatus::Pending;
    }

    public function refund(User $user, OrderReturn $return): bool
    {
        return $user->can('manage_orders')
            && $return->status === OrderReturnStatus::Approved;
    }
}