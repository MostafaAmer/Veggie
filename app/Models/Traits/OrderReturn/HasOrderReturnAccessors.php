<?php

namespace App\Models\OrderReturn\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\OrderReturnStatus;
use App\Enums\RefundMethod;
use Illuminate\Support\Str;

trait HasOrderReturnAccessors
{
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status instanceof OrderReturnStatus
                ? $this->status->label()
                : Str::title((string) $this->status)
        );
    }

    protected function refundMethodLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->refund_method instanceof RefundMethod
                ? $this->refund_method->label()
                : Str::title((string) $this->refund_method)
        );
    }

    protected function isFullyRefunded(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->refund_amount >= $this->calculateExpectedRefund()
        );
    }
}
