<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseOrderRequest;

class UpdateShippingRequest extends BaseOrderRequest
{
    protected function ability(): string
    {
        return 'updateShipping';
    }

    public function rules(): array
    {
        return [
            'tracking_number'    => ['required', 'string', 'max:50'],
            'shipping_carrier'   => ['required', 'string', 'max:50'],
            'shipping_details'   => ['nullable', 'array'],
            'estimated_delivery' => ['nullable', 'date'],
        ];
    }
}
