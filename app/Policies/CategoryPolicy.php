<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_categories');
    }

    public function view(User $user, Category $category): bool
    {
        return $category->is_active || $user->hasPermissionTo('manage_categories');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_categories');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('manage_categories');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('manage_categories');
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('manage_categories');
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('manage_categories');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermissionTo('manage_categories');
    }
}