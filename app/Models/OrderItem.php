<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\OrderItem\Traits\{HasOrderItemRelations, HasOrderItemAccessors, HasOrderItemScopes, HasOrderItemActions};
use App\Enums\Order\OrderItemStatus;

class OrderItem extends Model
{
    use SoftDeletes;
    use HasOrderItemRelations;
    use HasOrderItemAccessors;
    use HasOrderItemScopes;
    use HasOrderItemActions;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id','product_id','variant_id',
        'product_name','product_image','quantity',
        'price','original_price','discount','total',
        'weight','dimensions','attributes','custom_options',
        'notes','status','return_reason','refund_amount',
    ];

     protected $casts = [
        'price'           => 'decimal:2',
        'original_price'  => 'decimal:2',
        'discount'        => 'decimal:2',
        'total'           => 'decimal:2',
        'weight'          => 'decimal:2',
        'refund_amount'   => 'decimal:2',
        'quantity'        => 'integer',
        'attributes'      => 'array',
        'custom_options'  => 'array',
        'status'          => OrderItemStatus::class,
    ];

    protected $attributes = [
        'status'         => OrderItemStatus::Pending->value,
        'quantity'       => 1,
        'price'          => 0,
        'original_price' => 0,
        'discount'       => 0,
        'total'          => 0,
        'refund_amount'  => 0,
    ];

    protected $appends = [
        'formatted_price',
        'formatted_original_price',
        'formatted_discount',
        'formatted_total',
        'formatted_refund_amount',
        'status_label',
        'can_return',
    ];
}