<?php

namespace App\Observers;

use App\Enums\OrderItemStatus;
use App\Models\OrderItem;
use Illuminate\Support\Str;

class OrderItemObserver
{
    public function creating(OrderItem $item): void
    {
        if (!$item->getKey()) {
            $item->{$item->getKeyName()} = Str::uuid()->toString();
        }

        $item->quantity       = $item->quantity ?? 1;
        $item->discount       = $item->discount ?? 0.0;
        $item->product_name   = $item->product_name ?: $item->product?->name;
        $item->original_price = $item->original_price ?: $item->price;
        $item->total          = $this->calculateTotal($item);
    }

    public function created(OrderItem $item): void
    {
        if ($item->product) {
            $item->product->decreaseStock($item->quantity);
        }
    }

    public function updated(OrderItem $item): void
    {
        if ($item->wasChanged('status')) {
            $old = OrderItemStatus::from($item->getOriginal('status'));
            $new = OrderItemStatus::from($item->status);

            if ($new->isReturnable() && ! $old->isReturnable()) {
                $item->product?->increaseStock($item->quantity);
            }
        }
    }

    public function forceDeleted(OrderItem $item): void
    {
        $item->product?->increaseStock($item->quantity);
    }

    protected function calculateTotal(OrderItem $item): float
    {
        return ($item->price * $item->quantity) - $item->discount;
    }
}
