<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\{PaymentStatus, PaymentMethod};
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Payment\{BaseModel, HandlesPaymentState, HasFormattedAttributes, 
    HasIdempotencyKey, HasPaymentRelations, HasUuids};


class Payment extends Model
{
    use BaseModel, HandlesPaymentState, 
        HasFormattedAttributes, HasIdempotencyKey, HasPaymentRelations, 
        HasUuids, SoftDeletes;

    protected $keyType     = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'order_id',
        'user_id',
        'parent_id',
        'amount',
        'refunded_amount',
        'currency',
        'method',
        'gateway',
        'idempotency_key',
        'transaction_id',
        'status',
        'payment_details',
        'gateway_response',
        'error_code',
        'error_data',
        'paid_at',
    ];
    
    protected $casts = [
        'order_id'          => 'string',
        'user_id'           => 'string',
        'parent_id'         => 'string',
        'amount'            => 'decimal:2',
        'refunded_amount'   => 'decimal:2',
        'currency'          => 'string',
        'method'            => PaymentMethod::class,
        'gateway'           => 'string',
        'status'            => PaymentStatus::class,
        'idempotency_key'   => 'string',
        'payment_details'   => 'array',
        'gateway_response'  => 'array',
        'error_code'        => 'string',
        'error_data'        => 'array',
        'paid_at'           => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    protected $appends = [
        'formatted_amount',
        'method_label',
        'status_label'
    ];
}