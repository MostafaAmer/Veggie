<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true; 
        }

        return null; 
    }

    public function viewAny(User $user): bool
    {
        return $user->can('manage_payments');
    }

    public function create(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    public function update(User $user, Payment $payment): bool
    {
        return $payment->user_id === $user->id
            && $payment->status === PaymentStatus::Pending;
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $user->can('refund_payments')
            && $payment->isRefundable();
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->can('manage_payments');
    }

}
