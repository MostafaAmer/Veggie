<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ReturnItemCondition;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\OrderReturnItem\Traits\{HasOrderReturnItemAccessors, HasOrderReturnItemRelations};

class OrderReturnItem extends Model
{
    use HasOrderReturnItemRelations;
    use HasOrderReturnItemAccessors;

    protected $fillable = [
        'order_return_id',
        'order_item_id',
        'quantity',
        'reason',
        'condition',
        'restocked'
    ];

    protected $casts = [
        'condition' => ReturnItemCondition::class,
        'restocked' => 'boolean',
    ];

    protected $appends = [
        'condition_label',
        'total_refund_amount',
    ];

    public function getTotalRefundAmountAttribute(): float
    {
        return $this->quantity * $this->orderItem->price;
    }
}