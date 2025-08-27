<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseOrderRequest extends FormRequest
{
    protected string $orderParam = 'order';

    abstract protected function ability(): string;

    public function authorize(): bool
    {
        $order = $this->route($this->orderParam);

        return $order
            && $this->user()->can($this->ability(), $order);
    }
}