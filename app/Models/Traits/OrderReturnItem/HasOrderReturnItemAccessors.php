<?php

namespace App\Models\OrderReturnItem\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\ReturnItemCondition;

trait HasOrderReturnItemAccessors
{
    protected function conditionLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->condition instanceof ReturnItemCondition
                ? $this->condition->label()
                : Str::title((string) $this->condition)
        );
    }
}
