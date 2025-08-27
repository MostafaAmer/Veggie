<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderReturn\Traits\{HasOrderReturnAccessors, HasOrderReturnRelations, HasOrderReturnScopes, HasOrderReturnActions};
use App\Enums\OrderReturnStatus;
use App\Enums\RefundMethod;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};

class OrderReturn extends Model
{
    use HasOrderReturnRelations;
    use HasOrderReturnAccessors;
    use HasOrderReturnScopes;
    use HasOrderReturnActions;

    protected $fillable = [
        'order_id',
        'user_id',
        'status',
        'reason',
        'refund_amount',
        'refund_method',
        'bank_details',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'refunded_by',
        'refunded_at',
        'refund_notes',
    ];

    protected $casts = [
        'status'         => OrderReturnStatus::class,
        'refund_method'  => RefundMethod::class,
        'bank_details'   => 'array',
        'approved_at'    => 'datetime',
        'rejected_at'    => 'datetime',
        'refunded_at'    => 'datetime',
        'refund_amount'  => 'decimal:2',
    ];

    protected $appends = [
        'status_label',
        'refund_method_label',
        'is_fully_refunded',
    ];
}