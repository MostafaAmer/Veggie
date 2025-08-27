<?php

namespace App\Services\Calculators;

use App\Contracts\DiscountCalculatorContract;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;

class DiscountCalculator implements DiscountCalculatorContract
{
    public function calculateForOrder(Coupon $coupon, Order $order): float
    {
        $amount = $this->getApplicableAmount(
            // transform Eloquent items into array shape
            $order->items->map(fn($i) => [
                'product_id' => $i->product_id,
                'price'      => $i->price,
                'quantity'   => $i->quantity,
                'category_id'=> $i->product->category_id,
            ])->all(),
            $coupon,
            $order->subtotal
        );

        return $coupon->calculateDiscount($amount, $order);
    }

   /**
     * @param  array{product_id:int,price:float,quantity:int}[]  $items
     */
    public function calculateForCart(Coupon $coupon, array $items, float $subtotal): float
    {
        $amount = $this->getApplicableAmount(
            // ensure category_id is available if needed
            array_map(function($i) {
                return $i + ['category_id' => $i['category_id'] ?? null];
            }, $items),
            $coupon,
            $subtotal
        );

        return $coupon->calculateDiscount($amount);
    }


    /**
     * @param  array{product_id:int,price:float,quantity:int,category_id?:int}[]  $items
     */
    protected function getApplicableAmount(array $items, Coupon $coupon, float $total): float
    {
        if ($coupon->scope === 'global') {
            return $total;
        }

        /** @var Collection<int,array> $collection */
        $collection = collect($items);

        $filtered = match ($coupon->scope) {
            'category' => $collection->filter(fn($i) =>
                in_array($i['category_id'], $coupon->applicable_items, true)
            ),
            'product'  => $collection->filter(fn($i) =>
                in_array($i['product_id'], $coupon->applicable_items, true)
            ),
        };

        return $filtered->sum(fn($i) => $i['price'] * $i['quantity']);
    }
}