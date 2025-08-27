<?php

namespace App\Models\OrderItem\Traits;

use App\Enums\OrderItemStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasOrderItemAccessors
{
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatMoney($this->price)
        );
    }

    protected function formattedOriginalPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatMoney($this->original_price)
        );
    }

    protected function formattedDiscount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatMoney($this->discount)
        );
    }

    protected function formattedTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatMoney($this->total)
        );
    }

    protected function formattedRefundAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => is_null($this->refund_amount) ? null : $this->formatMoney($this->refund_amount)
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status instanceof OrderItemStatus
                ? $this->status->label()
                : (is_string($this->status) ? ucfirst($this->status) : '')
        );
    }

    protected function productImage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?: optional($this->product)->main_image_url,
            set: fn ($value) => $value
        );
    }

    private function formatMoney(mixed $value): string
    {
        $currency = config('app.currency', 'EGP');
        $amount   = $value ? (float) $value : 0.0;

        return number_format($amount, 2) . ' ' . $currency;
    }
}
