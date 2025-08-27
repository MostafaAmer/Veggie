<?php

namespace App\Models\OrderReturn\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Enums\OrderReturnStatus;

trait HasOrderReturnScopes
{
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderReturnStatus::Pending);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', OrderReturnStatus::Approved);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', OrderReturnStatus::Rejected);
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', OrderReturnStatus::Refunded);
    }
}
