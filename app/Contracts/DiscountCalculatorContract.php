<?php

namespace App\Contracts;

use App\Models\Coupon;
use App\Models\Order;

interface DiscountCalculatorContract
{
    public function calculateForOrder(Coupon $coupon, Order $order): float;

    /**
     * Calculate discount when previewing in cart.
     *
     * @param  array{product_id:int,price:float,quantity:int}[]  $items
     * @param  float                                            $subtotal
     */
    public function calculateForCart(Coupon $coupon, array $items, float $subtotal): float;
}