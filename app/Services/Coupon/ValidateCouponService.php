<?php

namespace App\Services\Coupon;

use App\Models\Coupon;
use App\Models\User;
use App\Contracts\DiscountCalculatorContract;
use Illuminate\Support\Collection;

class ValidateCouponService
{
    public function __construct(
        private DiscountCalculatorContract $calculator
    ) {}

    /**
     * @param  array{product_id:int,price:float,quantity:int}[]  $items
     * @return array{valid:bool, message:string, coupon?:Coupon, required_amount?:float}
     */
    public function validate(string $code, array $items, ?User $user = null): array
    {
        $coupon = Coupon::byCode($code)
            ->valid()
            ->when($user, fn($q) => $q->forUser($user->id))
            ->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Coupon not found or expired'];
        }

        if ($user && !$coupon->canBeUsedBy($user)) {
            return ['valid' => false, 'message' => 'You have already used this coupon'];
        }

        $subtotal = collect($items)
            ->sum(fn($i) => $i['price'] * $i['quantity']);

        if ($coupon->min_order_value > 0  && $subtotal < $coupon->min_order_value) {
            return [
                'valid'           => false,
                'message'         => 'Minimum order amount not reached',
                'required_amount' => $coupon->min_order_value - $subtotal
            ];
        }

        $potentialDiscount = $this->calculator->calculateForCart($coupon, $items, $subtotal);

        if ($potentialDiscount <= 0) {
            return ['valid' => false, 'message' => 'Coupon not applicable to any items'];
        }

        return ['valid' => true, 'coupon' => $coupon, 'message' => 'Coupon is valid'];
    }
}
