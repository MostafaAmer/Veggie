<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\Collection;
use App\Models\CartItem;

class RedisCartRepository implements CartRepositoryInterface
{
    protected function key(int $userId): string
    {
        return "cart:{$userId}";
    }

    public function getCartForUser(int $userId): Collection
    {
        $items = Redis::hGetAll($this->key($userId));
        return collect($items)->map(function($qty, $productId) {
            return new CartItem([
                'product_id' => (int)$productId,
                'quantity'   => (int)$qty,
            ]);
        });
    }

    public function addItem(int $userId, int $productId, int $qty): CartItem
    {
        Redis::hIncrBy($this->key($userId), $productId, $qty);
        return new CartItem(compact('productId', 'qty'));
    }

    public function removeItem(int $userId, int $productId): bool
    {
        return Redis::hDel($this->key($userId), $productId) > 0;
    }

    public function clearCart(int $userId): void
    {
        Redis::del($this->key($userId));
    }
}