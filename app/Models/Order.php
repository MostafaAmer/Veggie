<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Observers\OrderObserver;
use App\Enums\OrderStatus;
use App\Models\Traits\{HasOrderScopes, HasOrderAccessors,HasOrderActions, HasOrderRelations};

class Order extends Model
{
    use HasFactory,
        HasOrderAccessors,
        HasOrderActions,
        HasOrderRelations,
        HasOrderScopes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id','address_id','coupon_id','status','reference_number',
        'latitude','longitude','address','payment_method','payment_status',
        'is_paid','payment_id','paid_at','estimated_delivery_time',
        'subtotal','tax','discount','delivery_fee','total',
        'cancellation_reason','cancelled_by',
        'confirmed_at','processed_at','shipped_at','delivered_at',
        'returned_at','refunded_at',
        'tracking_number','shipping_carrier','shipping_details',
    ];

    protected $casts = [
        'id'                     => 'string',
        'latitude'               => 'decimal:8',
        'longitude'              => 'decimal:8',
        'subtotal'=>'decimal:2','tax'=>'decimal:2',
        'discount'=>'decimal:2','delivery_fee'=>'decimal:2',
        'total'=>'decimal:2', 'is_paid'=>'boolean',
        'paid_at'=>'datetime','confirmed_at'=>'datetime',
        'processed_at'=>'datetime','shipped_at'=>'datetime',
        'delivered_at'=>'datetime','returned_at'=>'datetime',
        'refunded_at'=>'datetime','estimated_delivery_time'=>'datetime',
        'address'=>'array','shipping_details'=>'array',
    ];

    protected $attributes = [
        'status'         => OrderStatus::Pending->value,
        'payment_status' => OrderStatus::Pending->value,
        'subtotal'=>0,'tax'=>0,'discount'=>0,'delivery_fee'=>0,'total'=>0,'is_paid'=>false,
    ];

    protected $appends = [
        'status_label',
        'payment_method_label',
        'payment_status_label',
        'shipping_details',
        'formatted_subtotal',
        'formatted_tax',
        'formatted_delivery_fee',
        'formatted_discount',
        'formatted_total',
        'is_delayed',
        'can_cancel',
        'can_return',
    ];
    
    protected static function booted()
    {
        static::observe(OrderObserver::class);
    }
}