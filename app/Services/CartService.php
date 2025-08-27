<?php

namespace App\Services;

use App\Models\{Cart, CartItem, Order};
use Illuminate\Support\Facades\DB;
use App\Events\OrderCreated;

class CartService
{
    public function getActiveCartForUser(int $userId): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $userId, 'status' => 'active'],
            ['status'  => 'active']
        );
    }

    public function addItem(Cart $cart, int $productId, int $qty, float $price, array $meta = []): CartItem
    {
        return DB::transaction(function () use ($cart, $productId, $qty, $price, $meta) {
            $item = $cart->items()->updateOrCreate(
                ['cart_id' => $cart->id, 'product_id' => $productId],
                ['quantity' => DB::raw("GREATEST(quantity + $qty, 1)"), 'price' => $price, 'metadata' => $meta]
            );
            return $item;
        });
    }

    public function updateItemQuantity(CartItem $item, int $qty): CartItem
    {
        $item->update(['quantity' => max(1, $qty)]);
        return $item;
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function checkout(Cart $cart, array $orderData): Order
    {
        return DB::transaction(function () use ($cart, $orderData) {
            $order = Order::create(array_merge($orderData, [
                'user_id'   => $cart->user_id,
                'total'     => $cart->items->sum(fn($i) => $i->quantity * $i->price),
            ]));

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->price,
                    'metadata'   => $item->metadata,
                ]);
            }

            $cart->update(['status' => 'ordered']);

            event(new OrderCreated($order));

            return $order;
        });
    }
}