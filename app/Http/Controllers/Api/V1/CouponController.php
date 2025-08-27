<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\Coupon\{ValidateCouponService, ApplyCouponService, RemoveCouponService};
use App\Http\Requests\{ValidateCouponRequest, ApplyCouponRequest};
use Illuminate\Http\{Request, JsonResponse};

class CouponController extends Controller
{
    public function __construct(
        private ValidateCouponService $validator,
        private CouponController    $calculator,
        private ApplyCouponService    $applier,
        private RemoveCouponService   $remover
    ) {
        $this->middleware('auth:sanctum');
    }

    public function validateCoupon(ValidateCouponRequest $request): JsonResponse
    {
        $data   = $request->validated();
        $result = $this->validator->validate(
            $data['code'],
            $data['items'],
            $request->user()
        );

        return response()->json($result);
    }

    public function calculateDiscount(ValidateCouponRequest $request): JsonResponse
    {
        $data = $request->validated();
        $validation = $this->validator->calculate(
           $data['code'],
            $data['items'],
            $request->user()
        );

        if (! $validation['valid']) {
            return response()->json([
                'valid'    => false,
                'discount' => 0.0,
                'message'  => $validation['message'],
            ]);
        }

        $coupon   = $validation['coupon'];
        $subtotal = collect($data['items'])
            ->sum(fn($i) => $i['price'] * $i['quantity']);

        $discount = $this->calculator
            ->calculateForCart($coupon, $data['items'], $subtotal);

        return response()->json([
            'valid'    => true,
            'discount' => $discount,
            'coupon'   => $coupon,
            'message'  => 'Discount calculated successfully',
        ]);
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $data  = $request->validated();
        $order  = $request->user()->cart->activeOrder;
        $result = $this->applier->apply($order, $data['code']);

        return response()->json($result);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $order  = $request->user()->cart->activeOrder;
        $result = $this->remover->remove($order);

        return response()->json($result);
    }
}