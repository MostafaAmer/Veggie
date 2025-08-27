<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\User;

class CartPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Cart $cart): bool
    {
        return $cart->user_id === $user->id;
    }

    public function addItem(User $user, Cart $cart): bool
    {
        return $cart->user_id === $user->id && $cart->status === 'active';
    }

    public function updateItem(User $user, Cart $cart): bool
    {
        return $cart->user_id === $user->id && $cart->status === 'active';
    }

    public function removeItem(User $user, Cart $cart): bool
    {
        return $cart->user_id === $user->id && $cart->status === 'active';
    }

    public function clear(User $user, Cart $cart): bool
    {
        return $cart->user_id === $user->id && $cart->status === 'active';
    }

    public function checkout(User $user, Cart $cart): bool
    {
        return $cart->user_id === $user->id && $cart->status === 'active' && $cart->items()->count() > 0;
    }
}