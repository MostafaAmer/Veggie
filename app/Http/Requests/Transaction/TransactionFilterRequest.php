<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'             => ['nullable', 'string'],
            'status'           => ['nullable', 'string'],
            'payment_method'   => ['nullable', 'string'],
            'gateway'          => ['nullable', 'string'],
            'date_from'        => ['nullable', 'date'],
            'date_to'          => ['nullable', 'date'],
            'amount_min'       => ['nullable', 'numeric', 'min:0'],
            'amount_max'       => ['nullable', 'numeric', 'min:0'],
            'per_page'         => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}