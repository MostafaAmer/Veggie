<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Transaction;

class ApproveTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');
        return $transaction && $this->user()->can('approve', $transaction);
    }

    public function rules(): array
    {
        return [
            'notes'            => ['nullable', 'string', 'max:1000'],
            'metadata'         => ['nullable', 'array'],
            'metadata.*'       => ['string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'notes'    => __('Transaction.notes'),
            'metadata' => __('Transaction.metadata'),
        ];
    }
}