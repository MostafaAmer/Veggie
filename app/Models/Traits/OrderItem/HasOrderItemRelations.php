<?php

namespace App\Models\OrderItem\Traits;

use App\Models\{Order, Product};
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasOrderItemRelations
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id')->withTrashed();
    }
}
