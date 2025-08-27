<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Enums\OrderStatus;

trait HasOrderScopes
{
    public function scopePending(Builder $query) { return $query->where('status', OrderStatus::Pending->value); }
    public function scopeConfirmed(Builder $query) { return $query->where('status',  OrderStatus::Confirmed->value); }
    public function scopeProcessing(Builder $query) { return $query->where('status', OrderStatus::Processing->value); }
    public function scopeShipped(Builder $query) { return $query->where('status', OrderStatus::Shipped->value); }
    public function scopeDelivered(Builder $query) { return $query->where('status', OrderStatus::Delivered->value); }
    public function scopeCancelled(Builder $query) { return $query->where('status', OrderStatus::Cancelled->value); }
    public function scopeReturned(Builder $query) { return $query->where('status', OrderStatus::Returned->value); }
    public function scopeRefunded(Builder $query) { return $query->where('status',  OrderStatus::Refunded->value); }

    public function scopePaid(Builder $query) { return $query->where('is_paid', true); }
    public function scopeUnpaid(Builder $query) { return $query->where('is_paid', false); }

    public function scopeByUser(Builder $query, $userId) { return $query->where('user_id', $userId); }
    public function scopeRecent(Builder $query, $days = 30) { return $query->where('created_at', '>=', now()->subDays($days)); }
    public function scopeWithCoupon(Builder $query) { return $query->whereNotNull('coupon_id'); }

    public function scopeNeedsAction(Builder $query)
    {
        return $query->whereIn('status', [
            OrderStatus::Pending->value,
            OrderStatus::Confirmed->value,
            OrderStatus::Processing->value
        ]);
    }

    public function scopeNeedsShippingUpdate(Builder $query)
    {
        return $query->whereIn('status', [
            OrderStatus::Processing->value,
            OrderStatus::Shipped->value
        ])->where(function ($q) {
            $q->whereNull('tracking_number')
              ->orWhere('estimated_delivery_time', '<', now());
        });
    }
}