<?php

namespace App\Models\Traits;

trait HasStockManagement
{
    public function increaseStock(int $quantity = 1): void
    {
        $this->increment('stock', $quantity);
    }

    public function decreaseStock(int $quantity = 1): void
    {
        $this->decrement('stock', $quantity);
        $this->increment('sold_count', $quantity);
    }

    public function getInventoryHistoryAttribute()
    {
        return $this->inventoryLogs()->latest()->get();
    }
}