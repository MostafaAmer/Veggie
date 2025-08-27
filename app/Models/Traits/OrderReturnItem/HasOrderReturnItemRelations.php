<?php

namespace App\Models\OrderReturnItem\Traits;

use App\Models\OrderItem;
use App\Models\OrderReturn;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasOrderReturnItemRelations
{
    public function orderReturn(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
}
