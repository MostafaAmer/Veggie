<?php

namespace App\Models\Traits;

use App\Enums\{OrderStatus, PaymentMethod, PaymentStatus};
use Carbon\Carbon;

trait HasOrderAccessors
{
    public function getInvoiceNumberAttribute(): string
    {
        return 'INV-' . $this->created_at->format('Ymd') . '-' . $this->id;
    }

    public function getTaxAmountAttribute(): float
    {
        return $this->subtotal * ($this->tax_rate / 100);
    }

    public function getShippingDetailsAttribute(): array
    {
        return [
            'carrier' => $this->shipping_carrier,
            'tracking_number' => $this->tracking_number,
            'estimated_delivery' => $this->estimated_delivery_time?->format('Y-m-d'),
            'details' => $this->shipping_details
        ];
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2) . ' ' . config('app.currency', 'EGP');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 2) . ' ' . config('app.currency', 'EGP');
    }

    public function getFormattedDeliveryFeeAttribute(): string
    {
        return number_format($this->delivery_fee, 2) . ' ' . config('app.currency', 'EGP');
    }

    public function getFormattedTaxAttribute(): string
    {
        return number_format($this->tax, 2) . ' ' . config('app.currency', 'EGP');
    }

    public function getFormattedDiscountAttribute(): string
    {
        return number_format($this->discount, 2) . ' ' . config('app.currency', 'EGP');
    }

    public function getStatusLabelAttribute(): string
    {
        return OrderStatus::tryFrom($this->status)?->label() ?? ucfirst($this->status);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return PaymentMethod::tryFrom($this->payment_method)?->label() ?? ucfirst(str_replace('_', ' ', $this->payment_method));
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return PaymentStatus::tryFrom($this->payment_status)?->label() ?? ucfirst(str_replace('_', ' ', $this->payment_status));
    }

    public function getIsDelayedAttribute(): bool
    {
        if (!$this->estimated_delivery_time || $this->status === OrderStatus::Delivered->value) {
            return false;
        }

        return Carbon::now()->gt($this->estimated_delivery_time);
    }

    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, [
            OrderStatus::Pending->value,
            OrderStatus::Confirmed->value,
            OrderStatus::Processing->value
        ]) && $this->created_at->diffInHours(now()) <= 24;
    }

    public function getCanReturnAttribute(): bool
    {
        return $this->status === OrderStatus::Delivered->value &&
               $this->delivered_at?->diffInDays(now()) <= config('orders.return_days', 14);
    }
}