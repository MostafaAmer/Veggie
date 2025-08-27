<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'product_id',
        'quantity_before',
        'quantity_after',
        'change',
        'reason',
        'created_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}