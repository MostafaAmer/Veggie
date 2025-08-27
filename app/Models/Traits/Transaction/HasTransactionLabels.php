<?php

namespace App\Models\Traits;

use App\Enums\{TransactionStatus, TransactionType};

trait HasTransactionLabels
{
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    public function getTypeLabelAttribute(): string
    {
        return TransactionType::getDescription($this->type) ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute(): string
    {
        return TransactionStatus::getDescription($this->status) ?? ucfirst($this->status);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return trans('payment.methods.' . $this->payment_method);
    }
}