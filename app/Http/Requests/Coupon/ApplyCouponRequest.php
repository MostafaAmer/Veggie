<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Coupon;
use Illuminate\Validation\Rule;

class ApplyCouponRequest extends FormRequest
{
    public ?Coupon $coupon = null;

    public function authorize(): bool
    {
        return $this->user() !== null
            && method_exists($this->user(), 'hasActiveCart')
            && $this->user()->hasActiveCart();
    }

    protected function prepareForValidation(): void
    {
        $code = $this->input('code');
        if (is_string($code)) {
            $this->coupon = Coupon::byCode($code)
                ->valid()
                ->forUser($this->user()->id)
                ->first();
        }
    }

    public function rules(): array
    {
        return [
            'code' => [
                'bail',
                'required',
                'string',
                'max:50',
                Rule::exists('coupons', 'code')
                    ->where(fn ($query) => $query
                        ->active()
                        ->valid()
                        ->forUser($this->user()->id)
                    )
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.exists'   => 'هذا الكود غير صالح أو انتهت صلاحيته.',
            'code.required' => 'يرجى إدخال كود القسيمة.',
            'code.max'      => 'كود القسيمة يجب ألا يتجاوز 50 حرفاً.',
        ];
    }
}