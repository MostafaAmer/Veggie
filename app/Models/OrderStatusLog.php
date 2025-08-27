<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\OrderStatus;
use Illuminate\Support\Str;

class OrderStatusLog extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps   = false;

    protected $fillable = [
        'order_id',
        'status',
        'notes',
        'changed_by'
    ];

    protected $casts = [
        'status'     => OrderStatus::class,
        'changed_at' => 'datetime'
    ];

    protected $appends = [
        'status_label'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => OrderStatus::tryFrom($this->status)?->label()
                ?? ucfirst((string) $this->status)
        );
    }

    protected static function boot()
    {
        static::creating(function (OrderStatusLog $log) {
            $log->id         = (string) Str::uuid();
            $log->changed_at = $log->changed_at ?? now();
        });
    }

   public function scopeForOrder(Builder $query, string $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }
}