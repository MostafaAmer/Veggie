<?php

namespace App\Services\Coupon;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class RemoveCouponService
{
    /**
     * @return array{success:bool, message:string}
     */
    public function remove(Order $order): array
    {
        if (! $order->coupon) {
            return [
                'success' => false,
                'message' => 'No coupon applied to this order',
            ];
        }

        $coupon = $order->coupon;
        $user   = $order->user;


        DB::transaction(function () use ($order, $coupon, $user) {
            $order->update([
                'coupon_id'       => null,
                'discount_amount' => 0.0,
                'total'           => $order->subtotal + $order->shipping_cost,
            ]);
            $coupon->decrementUsage($user);
        });

        return ['success' => true, 'message' => 'Coupon removed successfully'];
    }
}
