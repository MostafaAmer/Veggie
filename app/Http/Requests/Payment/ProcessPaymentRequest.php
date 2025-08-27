<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\PaymentMethod;


class ProcessPaymentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('payment_method')) {
            $this->merge([
                'payment_method' => strtolower($this->input('payment_method')),
            ]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->can('process', $this->route('order'));
    }

    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->methodSpecificRules()
        );
    }

    protected function baseRules(): array
    {
        return [
            'payment_method'    => ['required', Rule::in(PaymentMethod::values())],
            'payment_details'   => ['required', 'array'],
        ];
    }

    protected function methodSpecificRules(): array
    {
        $method = $this->input('payment_method');

        return match ($method) {
            PaymentMethod::CreditCard->value  => $this->creditCardRules(),
            PaymentMethod::Wallet->value,
            PaymentMethod::BankTransfer->value   => $this->digitalWalletRules(),
            default                           => [],
        };
    }

    protected function creditCardRules(): array
    {
        return [
            'payment_details.card_number'       => ['required', 'digits_between:13,19'],
            'payment_details.expiry_month'      => ['required', 'integer', 'between:1,12'],
            'payment_details.expiry_year'       => ['required', 'integer', 'digits:4'],
            'payment_details.cvv'               => ['required', 'digits_between:3,4'],
        ];
    }

    protected function digitalWalletRules(): array
    {
        return [
            'payment_details.transaction_id'    => ['required', 'string'],
            'payment_details.wallet_type'       => ['nullable', 'string'], // optional
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_details.card_number'    => 'card number',
            'payment_details.expiry_month'   => 'expiration month',
            'payment_details.expiry_year'    => 'expiration year',
            'payment_details.transaction_id' => 'wallet transaction ID',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in'                   => 'The selected payment method is not supported.',
            'payment_details.required'            => 'Payment details are required.',
            'digits_between'                      => 'The :attribute must be between :min and :max digits.',
        ];
    }
}