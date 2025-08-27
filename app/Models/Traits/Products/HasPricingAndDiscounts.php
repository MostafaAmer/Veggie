<?php

namespace App\Models\Traits;

use Illuminate\Support\Carbon;

trait HasPricingAndDiscounts
{
    public function getFinalPriceAttribute(): float
    {
        return $this->is_on_discount ? $this->discount_price : $this->price;
    }

    public function getIsOnDiscountAttribute(): bool
    {
        if (!$this->discount_price) return false;

        $now = Carbon::now();
        return (!$this->discount_start || $now->gte($this->discount_start)) &&
               (!$this->discount_end   || $now->lte($this->discount_end));
    }

    public function getDiscountPercentageAttribute(): ?float
    {
        if (!$this->is_on_discount) return null;

        return round((($this->price - $this->discount_price) / $this->price) * 100, 2);
    }

    public function setDiscount($price, $start = null, $end = null): void
    {
        $this->update([
            'discount_price' => $price,
            'discount_start' => $start,
            'discount_end'   => $end
        ]);
    }

    public function removeDiscount(): void
    {
        $this->update([
            'discount_price' => null,
            'discount_start' => null,
            'discount_end'   => null
        ]);
    }

    public function getProfitMarginAttribute(): ?float
    {
        if (!$this->cost_price) return null;

        return round((($this->price - $this->cost_price) / $this->cost_price) * 100, 2);
    }
}