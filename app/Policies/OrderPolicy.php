<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Enums\OrderStatus;

class OrderPolicy
{
    use HandlesAuthorization;

    // Reusable status groups
    private const CANCELABLE_STATUSES  = [OrderStatus::Pending, OrderStatus::Confirmed];
    private const REFUNDABLE_STATUSES  = [OrderStatus::Cancelled, OrderStatus::Returned];
    private const SHIPPABLE_STATUSES   = [OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Shipped];


    public function viewAny(User $user): bool
    {
        return $user->can('manage_orders');
    }

    public function view(User $user, Order $order): bool
    {
        return $this->isOwner($user, $order)
            || $user->can('manage_orders');
    }

    public function create(User $user): bool
    {
        return $user->is_active && !$user->is_banned;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('manage_orders') 
            && !in_array($order->status, [OrderStatus::Delivered, OrderStatus::Cancelled], true);
    }

    public function cancel(User $user, Order $order): bool
    {
        if (! $this->isOwner($user, $order)
            || ! in_array($order->status, self::CANCELABLE_STATUSES, true)
        ) {
            return false;
        }
        $maxHours = $user->hasPremium() ? 0.05 : 0.30;
        $hoursSince = $order->created_at->diffInHours(now());
        
        return $hoursSince < $maxHours;
    }

    public function return(User $user, Order $order): bool
    {
        if (! $this->isOwner($user, $order)
            || $order->status !== OrderStatus::Delivered
            || is_null($order->delivered_at)
        ) {
            return false;
        }

        $allowedDays = config('orders.return_hour', 1);
        $daysSince   = $order->delivered_at->diffInDays(now());

        return $daysSince <= $allowedDays;

    }

    public function refund(User $user, Order $order): bool
    {
        return $user->can('manage_orders')
            && in_array($order->status, self::REFUNDABLE_STATUSES, true)
            && $order->is_paid
            && !$order->isFullyRefunded();
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('manage_orders') && $order->status === OrderStatus::Cancelled;
    }

    public function viewInvoice(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    public function viewShipping(User $user, Order $order): bool
    {
        return $this->isOwner($user, $order)
            || $user->can('manage_orders')
            || $user->can('view_shipping');
    }

    public function updateShipping(User $user, Order $order): bool
    {
        return $user->can('manage_orders') && 
               in_array($order->status, self::SHIPPABLE_STATUSES, true);
    }

    protected function isOwner(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
}