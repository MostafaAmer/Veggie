<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Enums\PaymentStatus;
use InvalidArgumentException;

trait HandlesPaymentState
{
    public function markAsCompleted(?string $transactionId = null): bool
    {
        return $this->update([
            'status'         => PaymentStatus::Paid,
            'transaction_id' => $transactionId ?? $this->transaction_id,
            'paid_at'        => now(),
        ]);
    }

    public function markAsFailed(string $reason): bool
    {
        $response = $this->gateway_response;
        $response['failure_reason'] = $reason;

        return $this->update([
            'status'           => PaymentStatus::Failed,
            'gateway_response' => $response,
            'error_code'       => $response['code'] ?? null,
            'error_data'       => $response['data'] ?? [],
        ]);
    }

    public function initiateRefund(float $amount = null): bool
    {
        $toRefund  = $amount ?? $this->amount;
        $available = $this->amount - $this->refunded_amount;

        if ($toRefund > $available) {
            throw new InvalidArgumentException('Cannot refund more than the available amount');
        }

        $newRefunded = $this->refunded_amount + $toRefund;
        $newStatus   = $newRefunded === $this->amount
            ? PaymentStatus::Refunded
            : PaymentStatus::PartiallyRefunded;

        return $this->update([
            'refunded_amount' => $newRefunded,
            'status'          => $newStatus,
        ]);
    }

    public function isRefundable(): bool
    {
        return $this->status === PaymentStatus::Paid
            && $this->refunded_amount < $this->amount;
    }
}