<?php

namespace App\Services\Coupon;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Contracts\DiscountCalculatorContract;
use App\Services\Coupon\ValidateCouponService;

class ApplyCouponService
{
    public function __construct(
        private ValidateCouponService $validator,
        private DiscountCalculatorContract $calculator
    ) {}

    /**
     * @return array{success:bool, discount:float, message:string, coupon?:\App\Models\Coupon}
     */
    public function apply(Order $order, string $code): array
    {
        $items = $order->items->map(fn($i) => [
            'product_id'=> $i->product_id,
            'price'     => $i->price,
            'quantity'  => $i->quantity,
        ])->all();

        $validation = $this->validator->validate($code, $items, $order->user);

        if (!$validation['valid']) {
            return ['success' => false, 'discount' => 0.0, 'message' => $validation['message']];
        }

        $coupon   = $validation['coupon'];
        $discount = $this->calculator->calculateForOrder($coupon, $order);

        DB::transaction(function () use ($order, $coupon, $discount) {
            $order->update([
                'coupon_id'       => $coupon->id,
                'discount_amount' => $discount,
                'total'           => $order->subtotal + $order->shipping_cost - $discount,
            ]);

            $coupon->incrementUsage($order->user);
        });

        return [
            'success'  => true,
            'discount' => $discount,
            'coupon'   => $coupon,
            'message'  => 'Coupon applied successfully'
        ];
    }
}
