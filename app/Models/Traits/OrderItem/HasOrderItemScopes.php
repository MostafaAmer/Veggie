<?php

namespace App\Models\OrderItem\Traits;

use App\Enums\OrderItemStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasOrderItemScopes
{
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderItemStatus::Pending->value);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', OrderItemStatus::Confirmed->value);
    }

    public function scopeShipped(Builder $query): Builder
    {
        return $query->where('status', OrderItemStatus::Shipped->value);
    }

    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('status', OrderItemStatus::Delivered->value);
    }

    public function scopeReturned(Builder $query): Builder
    {
        return $query->where('status', OrderItemStatus::Returned->value);
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', OrderItemStatus::Refunded->value);
    }

    public function scopePartiallyRefunded(Builder $query): Builder
    {
        return $query->where('status', OrderItemStatus::PartiallyRefunded->value);
    }

    public function scopeByOrder(Builder $query, string $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByProduct(Builder $query, string $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeCanReturn(Builder $query): Builder
    {
        $days = config('orders.return_days', 14);

        return $query
            ->where('status', OrderItemStatus::Delivered->value)
            ->whereHas('order', fn($q) => 
                $q->where('delivered_at', '>=', now()->subDays($days))
        );
    }
}
