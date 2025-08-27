<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Transaction;

class CancelTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');
        return $transaction && $this->user()->can('cancel', $transaction);
    }

    public function rules(): array
    {
        return [
            'reason'           => ['required', 'string', 'max:500'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reason' => __('Transaction.cancel_reason'),
            'notes'  => __('Transaction.notes'),
        ];
    }
}