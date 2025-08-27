<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\CartItem;

interface CartRepositoryInterface
{
    public function getCartForUser(int $userId): Collection;
    public function addItem(int $userId, int $productId, int $qty): CartItem;
    public function removeItem(int $userId, int $productId): bool;
    public function clearCart(int $userId): void;
}