<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseOrderRequest;
use Illuminate\Validation\Rule;
use App\Enums\OrderStatus;

class UpdateOrderStatusRequest extends BaseOrderRequest
{
    protected function ability(): string
    {
        return 'update';
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(
                    array_map(
                        fn(OrderStatus $s): string => $s->value,
                        OrderStatus::cases()
                    )
                ),
            ],
            'notes'  => ['nullable', 'string', 'max:500'],
        ];
    }
}
