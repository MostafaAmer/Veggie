<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Coupon;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class ValidateCouponRequest extends FormRequest
{
    public ?Coupon $coupon = null;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($code = $this->input('code')) {
            $this->coupon = Coupon::byCode($code)
                ->active()
                ->valid()
                ->forUser($this->user()->id)
                ->first();
        }
    }

    public function rules(): array
    {
        return [
            'code'       => [
                'bail',
                'required',
                'string',
                'max:50',
                Rule::exists('coupons', 'code')
                    ->where(fn ($query) => $query
                        ->active()
                        ->valid()
                        ->forUser($this->user()->id)
                    ),
            ],
            'items'      => ['bail', 'required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'items.*.price'      => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (! $this->coupon) {
                return; // exist rule سيرسل رسالة خاصة
            }

            // 1. تحقق من قيمة الحد الأدنى للطلب
            $subtotal = collect($this->input('items'))
                ->sum(fn ($i) => $i['price'] * $i['quantity']);

            if ($subtotal < $this->coupon->min_order_value) {
                $v->errors()->add('items', sprintf(
                    'يجب أن تكون قيمة الطلب على الأقل %s %s.',
                    number_format($this->coupon->min_order_value, 2),
                    config('app.currency')
                ));
            }

            // 2. تحقق من أقصى مبلغ للخصم
            if ($this->coupon->max_discount_amount !== null) {
                $discount = $this->coupon->calculateDiscount($subtotal, null);
                if ($discount > $this->coupon->max_discount_amount) {
                    $v->errors()->add('items', sprintf(
                        'الخصم لا يمكن أن يتجاوز %s %s.',
                        number_format($this->coupon->max_discount_amount, 2),
                        config('app.currency')
                    ));
                }
            }
            if ($this->coupon->scope->value !== 'global') {
                $allIds = collect($this->input('items'))
                    ->pluck($this->coupon->scope === \App\Enums\CouponScope::CATEGORY
                        ? 'category_id'
                        : 'product_id')
                    ->unique();

                $allowed = $this->coupon->{"{$this->coupon->scope->value}s"}()
                    ->pluck('id');

                if ($allIds->diff($allowed)->isNotEmpty()) {
                    $v->errors()->add('items', 'بعض العناصر غير مشمولة بهذه القسيمة.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'code.exists'     => 'هذا الكود غير صالح أو انتهت صلاحيته.',
            'items.required'  => 'يجب تمرير قائمة العناصر للتحقق.',
            'items.min'       => 'يجب أن يحتوي الطلب على عنصر واحد على الأقل.',
            'items.*.product_id.exists' => 'أحد المنتجات غير موجود في النظام.',
            'items.*.quantity.min'      => 'يجب أن تكون الكمية على الأقل 1.',
            'items.*.price.min'         => 'السعر يجب أن يكون 0 أو أكثر.',
        ];
    }
}