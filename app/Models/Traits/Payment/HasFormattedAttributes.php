<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;

trait HasFormattedAttributes
{
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    public function getMethodLabelAttribute(): string
    {
        return $this->method->label();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }
}