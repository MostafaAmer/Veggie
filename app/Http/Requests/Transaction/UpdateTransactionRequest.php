<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TransactionStatus;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');
        return $transaction && $this->user()->can('update', $transaction);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge(['status' => strtolower($this->input('status'))]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                 Rule::in(TransactionStatus::getValues()),
            ],
            'notes'                   => ['sometimes', 'string', 'max:1000'],
            'metadata'                => ['sometimes', 'array'],
            'metadata.*'              => ['string'],
            'gateway_response'        => ['sometimes', 'array'],
            'payment_method'          => ['sometimes', 'string', 'max:50'],
            'gateway_transaction_id'  => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status'                       => __('Transaction.status'),
            'notes'                        => __('Transaction.notes'),
            'metadata'                     => __('Transaction.metadata'),
            'gateway_response'             => __('Transaction.gateway_response'),
            'payment_method'               => __('Transaction.payment_method'),
            'gateway_transaction_id'       => __('Transaction.gateway_id'),
        ];
    }

    public function messages(): array
    {
        return [
            'status.in'                    => __('errors.transaction.status_invalid'),
            'notes.max'                    => __('errors.transaction.notes_max'),
            'metadata.array'               => __('errors.transaction.metadata_array'),
            'metadata.*.string'            => __('errors.transaction.metadata_string'),
            'gateway_response.array'       => __('errors.transaction.gateway_response_array'),
            'payment_method.max'           => __('errors.transaction.payment_method_max'),
            'gateway_transaction_id.max'   => __('errors.transaction.gateway_id_max'),
        ];
    }
}