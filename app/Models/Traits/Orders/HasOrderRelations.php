<?php

namespace App\Models\Traits;

use App\Models\{User, Coupon, OrderItem, Transaction, Payment, Cart};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasOrderRelations
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class)->withDefault();
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by')->withDefault();
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

   public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
}