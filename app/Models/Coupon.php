<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\{HasCouponScopes, HasCouponCalculations};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\{CouponType, CouponScope};

class Coupon extends Model
{
    use HasUuids, SoftDeletes, HasCouponScopes, HasCouponCalculations;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'type',
        'value',
        'scope',
        'min_order_value',
        'max_discount_amount',
        'max_uses',
        'max_uses_per_user',
        'is_reusable',
        'valid_from',
        'valid_to',
        'is_active',
        'user_id',
        'created_by',
    ];

    protected $casts = [
        'type'      => CouponType::class,
        'scope'     => CouponScope::class,
        'value'     => 'float',
        'min_order_value'    => 'float',
        'max_discount_amount' => 'float',
        'max_uses'   => 'integer',
        'used_count'=> 'integer',
        'max_uses_per_user' => 'integer',
        'is_reusable'=> 'boolean',
        'valid_from' => 'datetime',
        'valid_to'   => 'datetime',
        'is_active'  => 'boolean',
    ];

    protected $appends = [
        'type_label',
        'scope_label',
        'remaining_uses',
        'validity_period',
        'is_valid',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_user')
            ->withPivot('usage_count')
            ->withTimestamps();
    }

    public function getFormattedValueAttribute(): string
    {
        return $this->type === 'percentage' 
            ? "{$this->value}%"
            : number_format($this->value, 2) . ' ' . config('app.currency', 'EGP');
    }

    public function getRemainingUsesAttribute(): ?int
    {
        return $this->max_uses
            ? $this->max_uses - $this->used_count
            : null;
    }

    public function getValidityPeriodAttribute(): string
    {
        $from = $this->valid_from?->format('Y-m-d') ?: 'Any time';
        $to   = $this->valid_to?->format('Y-m-d')   ?: 'Forever';

        return "From {$from} to {$to}";
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type->name();
    }

    public function getScopeLabelAttribute(): string
    {
        return $this->scope->name();
    }

    public function getIsValidAttribute(): bool
    {
        return $this->canBeUsedBy(auth()->user());
    }
}