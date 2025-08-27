<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Transaction;
use App\Enums\{TransactionStatus, TransactionType};

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Transaction::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge(['status' => strtolower($this->input('status'))]);
        }

        if ($this->has('type')) {
            $this->merge(['type' => strtolower($this->input('type'))]);
        }
    }

    public function rules(): array
    {
        return [
            'order_id'                => ['required', 'uuid', 'exists:orders,id'],
            'user_id'                 => ['required', 'uuid', 'exists:users,id'],

            'type'                    => [
                'required',
                Rule::in(TransactionType::getValues()),
            ],
            'status'                  => [
                'sometimes',
                Rule::in(TransactionStatus::getValues()),
            ],

            'amount'                  => ['required', 'numeric', 'min:0.01'],
            'currency'                => ['required', 'string', 'size:3'],

            'payment_method'          => ['required', 'string', 'max:50'],
            'gateway'                 => ['sometimes', 'string', 'max:50'],
            'gateway_transaction_id'  => ['sometimes', 'string', 'max:100'],

            'gateway_response'        => ['sometimes', 'array'],
            'notes'                   => ['sometimes', 'string', 'max:1000'],

            'metadata'                => ['sometimes', 'array'],
            'metadata.*'              => ['string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'order_id'                => __('Transaction.order'),
            'user_id'                 => __('Transaction.user'),
            'type'                    => __('Transaction.type'),
            'status'                  => __('Transaction.status'),
            'amount'                  => __('Transaction.amount'),
            'currency'                => __('Transaction.currency'),
            'payment_method'          => __('Transaction.payment_method'),
            'gateway_transaction_id'  => __('Transaction.gateway_id'),
            'metadata'                => __('Transaction.metadata'),
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required'               => __('errors.transaction.order_required'),
            'order_id.uuid'                   => __('errors.transaction.order_uuid'),
            'order_id.exists'                 => __('errors.transaction.order_exists'),

            'user_id.required'                => __('errors.transaction.user_required'),
            'user_id.uuid'                    => __('errors.transaction.user_uuid'),
            'user_id.exists'                  => __('errors.transaction.user_exists'),

            'type.in'                         => __('errors.transaction.type_invalid'),
            'status.in'                       => __('errors.transaction.status_invalid'),

            'amount.numeric'                  => __('errors.transaction.amount_numeric'),
            'amount.min'                      => __('errors.transaction.amount_min'),

            'currency.size'                   => __('errors.transaction.currency_size'),

            'payment_method.max'              => __('errors.transaction.payment_method_max'),
            'gateway_transaction_id.max'      => __('errors.transaction.gateway_id_max'),

            'notes.max'                       => __('errors.transaction.notes_max'),
            'metadata.array'                  => __('errors.transaction.metadata_array'),
            'metadata.*.string'               => __('errors.transaction.metadata_string'),
        ];
    }
}