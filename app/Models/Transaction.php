<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\{TransactionStatus, TransactionType};
use App\Models\Traits\{HasTransactionLabels, HasTransactionScopes};

class Transaction extends Model
{
    use HasTransactionLabels, HasTransactionScopes;

    protected $fillable = [
        'order_id', 'user_id', 'processed_by', 'type', 'status', 'amount',
        'currency', 'payment_method', 'gateway_response', 'metadata', 'processed_at'
    ];

    protected $casts = [
        'type'             => TransactionType::class,
        'status'           => TransactionStatus::class,
        'amount'           => 'decimal:2',
        'gateway_response' => 'array',
        'metadata'         => 'array',
        'processed_at'     => 'datetime',
    ];

    protected $appends = [
        'formatted_amount',
        'type_label',
        'status_label',
        'payment_method_label'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(TransactionStatusLog::class);
    }
   
}