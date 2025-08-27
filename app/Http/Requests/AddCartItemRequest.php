<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Cart;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
       return $this->user()->can('addItem', Cart::class);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'price'      => ['required', 'numeric', 'min:0.01'],
            'metadata'   => ['sometimes', 'array'],
            'metadata.*' => ['string'],
        ];
    }
}