<?php

namespace App\Models\OrderReturn\Traits;

use App\Enums\OrderReturnStatus;
use App\Enums\RefundMethod;
use Illuminate\Support\Facades\DB;

trait HasOrderReturnActions
{
    public function calculateExpectedRefund(): float
    {
        return $this->items
            ->reduce(fn($sum, $item) => $sum + ($item->orderItem->price * $item->quantity), 0.0);
    }

    public function approve(int $approverId): void
    {
        DB::transaction(function () use ($approverId) {
            $this->update([
                'status'       => OrderReturnStatus::Approved,
                'approved_by'  => $approverId,
                'approved_at'  => now(),
            ]);
        });
    }

    public function reject(string $reason, int $rejectorId): void
    {
        $this->update([
            'status'           => OrderReturnStatus::Rejected,
            'rejection_reason' => $reason,
            'rejected_by'      => $rejectorId,
            'rejected_at'      => now(),
        ]);
    }

    public function refund(float $amount, RefundMethod $method, array $bankDetails = [], int $refunderId = null): void
    {
        DB::transaction(function () use ($amount, $method, $bankDetails, $refunderId) {
            if ($amount > $this->calculateExpectedRefund()) {
                throw new \InvalidArgumentException('Refund amount exceeds expected refund.');
            }

            $this->update([
                'status'        => OrderReturnStatus::Refunded,
                'refund_amount' => $amount,
                'refund_method' => $method,
                'bank_details'  => $bankDetails,
                'refunded_by'   => $refunderId,
                'refunded_at'   => now(),
            ]);
        });
    }
}
