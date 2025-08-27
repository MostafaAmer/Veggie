<?php

namespace App\Models\Traits;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait HasCouponCalculations
{
    public function calculateDiscount(float $subtotal, ?Order $order = null): float
    {
        return match ($this->type->value) {
            'percentage'    => round(($subtotal * $this->value) / 100, 2),
            'fixed'        => min($this->value, $subtotal),
            'free_shipping'=> $order?->shipping_cost ?? 0.0,
            'bogo'         => $this->calculateBogoDiscount($order),
            default        => 0.0,
        };
    }

    protected function calculateBogoDiscount(?Order $order): float
    {
        if (! $order) {
            return 0.0;
        }

        $items = $this->getApplicableItems($order->items)
                      ->sortBy('price')
                      ->values();

        $discount = 0.0;
        $pairs    = $items->chunk(2);

        foreach ($pairs as $pair) {
            if ($pair->count() === 2) {
                $discount += $pair->first()->price;
            }
        }

        return round($discount, 2);
    }

    protected function getApplicableItemsQuery(Collection $items): Collection
    {
        return match ($this->scope->value) {
            'category' => $items->whereIn('category_id', $this->categories->pluck('id')),
            'product'  => $items->whereIn('product_id', $this->products->pluck('id')),
            default    => $items,
        };
    }

    public function incrementUsage(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $this->increment('used_count');

            if ($this->users()->where('user_id', $userId)->exists()) {
                $this->users()
                     ->updateExistingPivot($userId, [
                         'usage_count' => DB::raw('usage_count + 1'),
                     ]);
            } else {
                $this->users()->attach($userId, ['usage_count' => 1]);
            }
        });
    }

    public function canBeUsedBy(\App\Models\User $user): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();
        if (($this->valid_from && $this->valid_from->isAfter($now)) ||
            ($this->valid_to   && $this->valid_to->isBefore($now))) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        $pivot = $this->users()
                      ->where('user_id', $user->id)
                      ->first()?->pivot
                      ->usage_count ?? 0;

        if ($this->max_uses_per_user !== null && $pivot >= $this->max_uses_per_user) {
            return false;
        }

        if ($this->user_id !== null && $this->user_id !== $user->id) {
            return false;
        }

        return true;
    }
}
