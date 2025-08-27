<?php

namespace App\Policies\Discounts;
use App\Models\Order;

class CouponPolicy
{
    /**
     * @param  Order  $order
     * @param  array{category_ids:int[], percentage:float}  $settings
     * @return float 
     */
    public function apply(Order $order, array $settings): float
    {
        $categoryIds = $settings['category_ids'] ?? [];
        $percentage  = $settings['percentage'] ?? 0.0;
        if (empty($categoryIds) || $percentage <= 0) {
            return 0.0;
        }

        $discountableItems = $order->items->filter(
            fn($item) => in_array(
                $item->product->category_id,
                $categoryIds,
                true
            )
        );

        $totalAmount = $discountableItems->sum(
            fn($item) => $item->price * $item->quantity
        );

        return round($totalAmount * ($percentage / 100), 2);
    }
}