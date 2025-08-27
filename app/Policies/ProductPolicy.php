<?php
declare(strict_types=1);
namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return $product->is_approved && $product->is_active
            || $user->hasRole(['admin','content_manager'])
            || $product->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('vendor') || $user->can('manage_products');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasRole(['admin','content_manager'])
            || ($user->hasRole('vendor') && $product->created_by === $user->id);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasRole(['admin', 'content_manager']);
    }

    public function approve(User $user): bool
    {
        return $user->hasRole(['admin', 'content_manager']);
    }

    public function viewStock(User $user, Product $product): bool
    {
        return $user->hasRole(['admin', 'inventory_manager']);
    }

    public function manageDiscounts(User $user, Product $product): bool
    {
        return $user->hasRole(['admin', 'marketing_manager']);
    }

    public function feature(User $user, Product $product): bool
    {
        return $user->hasRole(['admin', 'marketing_manager']);
    }

    public function manageInventory(User $user, Product $product): bool
    {
        return $user->hasRole(['admin', 'inventory_manager']);
    }

    public function managePricing(User $user, Product $product): bool
    {
        return $user->hasRole(['admin', 'pricing_manager']);
    }

    public function viewSalesData(User $user, Product $product): bool
    {
        return $user->hasRole(['admin', 'sales_manager']);
    }
}