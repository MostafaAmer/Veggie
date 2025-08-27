<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundPaymentRequest extends FormRequest
{
    protected float $maxRefund;

    protected function prepareForValidation(): void
    {
        $this->maxRefund = $this->route('payment')->refundableAmount();
    }

    public function authorize(): bool
    {
        return $this->user()->can('refund', $this->route('payment'));
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'nullable',
                'numeric',
                'min:0.1',
                'max:' . $this->maxRefund,
            ],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.max' => "Maximum refundable amount is {$this->maxRefund}.",
            'amount.min' => 'Refund amount must be at least 0.10.',
        ];
    }
    
    public function attributes(): array
    {
        return [
            'amount' => 'refund amount',
            'reason' => 'refund reason',
        ];
    }

}