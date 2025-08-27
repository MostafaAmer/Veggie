<?php

namespace App\Models\Traits;

use App\Enums\{TransactionStatus, TransactionType};

trait HasTransactionScopes
{
    public function scopePending($query)
    {
        return $query->where('status', TransactionStatus::Pending);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', TransactionStatus::Completed);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePayment($query)
    {
        return $query->where('type', TransactionType::Payment);
    }

    public function scopeRefund($query)
    {
        return $query->where('type', TransactionType::Refund);
    }
}