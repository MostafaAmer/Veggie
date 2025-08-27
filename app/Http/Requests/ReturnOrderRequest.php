<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ReturnQuantityRule;

class ReturnOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // $this->merge(['order_id' => $this->route('order')->id]);
    }

    public function authorize(): bool
    {
        $order = $this->route('order');
        return $order->user_id === $this->user()->id
            && $order->canReturn();
    }

    public function rules(): array
    {
        $orderId = $this->route('order')->id;

        return [
            'reason'               => 'required|string|max:500',
            'items'                => 'required|array|min:1',
            'items.*.order_item_id' => [
                'required',
                'distinct',
                Rule::exists('order_items', 'id')
                    ->where(fn($query) => $query->where('order_id', $orderId))
            ],
            'items.*.quantity'     => [
                'required',
                'integer',
                'min:1',
                new ReturnQuantityRule($orderId)
            ],
            'items.*.condition'             => ['nullable', Rule::in(['unopened','opened','damaged'])],
            'attachments'                   => 'sometimes|array|max:5',
            'attachments.*'                 => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
            'refund_method'                 => [
                'required_if:order.payment_method,!=,cash_on_delivery',
                Rule::in(['original_method','wallet_credit','bank_transfer'])
            ],
            'bank_details'                  => 'required_if:refund_method,bank_transfer|array',
            'bank_details.account_number'   => 'required_if:refund_method,bank_transfer|string|max:50',
            'bank_details.bank_name'        => 'required_if:refund_method,bank_transfer|string|max:100',
            'bank_details.iban'             => 'required_if:refund_method,bank_transfer|string|max:34',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required'               => 'يجب إدخال سبب الإرجاع',
            'items.required'                => 'يجب تحديد العناصر المراد إرجاعها',
            'items.*.order_item_id.exists'  => 'أحد العناصر غير موجود في الطلب',
            'items.*.order_item_id.distinct'=> 'لا يمكن تكرار نفس العنصر مرتين',
            'items.*.quantity.min'          => 'الكمية يجب أن تكون على الأقل 1',
            'attachments.*.max'             => 'لا يمكن رفع ملف أكبر من 2 ميجابايت',
            'refund_method.required_if'     => 'يجب اختيار طريقة الاسترداد',
        ];
    }

    public function attributes(): array
    {
        return [
            'items.*.quantity'              => 'الكمية',
            'bank_details.account_number'   => 'رقم الحساب',
            'bank_details.bank_name'        => 'اسم البنك',
            'bank_details.iban'             => 'رقم الآيبان',
        ];
    }
}
