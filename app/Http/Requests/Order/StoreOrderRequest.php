<?php
// app/Http/Requests/StoreOrderRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is_active && ! $this->user()->is_banned;
    }

    public function rules(): array
    {
        return [
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', Rule::exists('products', 'id')],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],

            'address_id'             => ['nullable', Rule::exists('addresses', 'id')],
            'coupon_code'            => ['nullable', 'string', Rule::exists('coupons', 'code')],
            'delivery_fee'           => ['nullable', 'numeric', 'min:0'],

            'payment_method'         => ['required', Rule::in(self::PAYMENT_METHODS)],
        ];
    }
}