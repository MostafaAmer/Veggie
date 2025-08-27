<?php

namespace App\Models\Traits;

use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

trait HasOrderActions
{
    public function recalculateTotals(): void
    {
        $this->load(['items', 'coupon']);
        $this->subtotal = $this->items->sum('total');
        $this->discount = $this->coupon ? $this->coupon->calculateDiscount($this->subtotal) : 0;
        $this->total = max(0, $this->subtotal + $this->tax + $this->delivery_fee - $this->discount);
        $this->save();
    }

    public function markAsPaid($paymentId = null): void
    {
        $this->update([
            'is_paid' => true,
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_id' => $paymentId ?? $this->payment_id
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'payment_status' => 'failed',
            'is_paid' => false
        ]);
    }

    public function changeStatus(string $status, ?string $notes = null, ?User $changedBy = null): void
    {
        DB::transaction(function () use ($status, $notes, $changedBy) {
            $timestampMap = [
                OrderStatus::Confirmed->value => 'confirmed_at',
                OrderStatus::Processing->value => 'processed_at',
                OrderStatus::Shipped->value => 'shipped_at',
                OrderStatus::Delivered->value => 'delivered_at',
                OrderStatus::Returned->value => 'returned_at',
                OrderStatus::Refunded->value => 'refunded_at'
            ];
            $this->update(['status' => $status]);

            if (isset($timestampMap[$status])) {
                $updates[$timestampMap[$status]] = now();
            }

            $this->statusLogs()->create([
                'status' => $status,
                'notes' => $notes,
                'changed_by' => $changedBy?->id
            ]);
        });
    }

    public function cancel(string $reason, ?User $cancelledBy = null): void
    {
        $this->changeStatus(OrderStatus::Cancelled->value, $reason, $cancelledBy);
        $this->update([
            'cancellation_reason' => $reason,
            'cancelled_by' => $cancelledBy?->id
        ]);
    }

    public function returnOrder(string $reason, ?User $processedBy = null): void
    {
        $this->changeStatus(OrderStatus::Returned->value, $reason, $processedBy);
    }

    public function refundOrder(string $reason, ?User $processedBy = null): void
    {
        $this->changeStatus(OrderStatus::Refunded->value, $reason, $processedBy);
        $this->update([
            'payment_status' => 'refunded',
            'refunded_at' => now()
        ]);
    }

    public function isFullyRefunded(): bool
    {
        return $this->transactions()
            ->where('type', 'refund')
            ->sum('amount') >= $this->total;
    }
}