<?php

namespace App\Models\OrderItem\Traits;

use App\Enums\OrderItemStatus;
use Illuminate\Support\Facades\DB;

trait HasOrderItemActions
{
    public function calculateTotal(): void
    {
        $price = (float) $this->price;
        $qty = (int) $this->quantity;
        $discount = (float) ($this->discount ?? 0);

        $total = max(0, ($price * $qty) - $discount);

        $this->forceFill(['total' => $total])->save();
    }

    public function changeStatus(OrderItemStatus $status, ?string $notes = null): void
    {
        $attributes = ['status' => $status->value];

        if (! is_null($notes)) {
            $attributes['notes'] = $notes;
        }

        $this->update($attributes);
    }

    public function canReturn(): bool
    {
        if ($this->status !== OrderItemStatus::Delivered) {
            return false;
        }

        $days = (int) config('orders.return_days', 14);
        $deliveredAt = $this->order?->delivered_at;

        return $deliveredAt
            ? $deliveredAt->diffInDays(now()) <= $days
            : false;
    }

    public function refund(float $amount, string $reason): void
    {
        DB::transaction(function () use ($amount, $reason) {
            $amount = max(0, $amount);
            $total = (float) $this->total;
            $refund  = min($amount, $total);

            if ($amount >= $total) {
                $this->changeStatus(OrderItemStatus::Refunded, $reason);
            } else {
                $this->changeStatus(OrderItemStatus::PartiallyRefunded, $reason);
            }
            $this->update(['refund_amount' => $refund]);
        });
    }
}
