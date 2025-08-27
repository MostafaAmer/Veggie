<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || 
               $user->hasPermission('view_users');    
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || 
               ($user->hasPermission('update_users') && 
                $user->level > $model->level);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id && 
               $user->hasPermission('delete_users') && 
               $user->level > $model->level;
    }

    public function changePassword(User $user, User $model): bool
    {
        return $user->id === $model->id && 
               is_null($model->provider);
    }

    public function verifyEmail(User $user, User $model): bool
    {
        return $user->id === $model->id && 
               is_null($model->email_verified_at) &&
               is_null($model->provider);
    }

    public function assignRoles(User $user): bool
    {
        return $user->hasPermission('assign_roles');
    }

    public function activate(User $user, User $model): bool
    {
        return $user->id !== $model->id && 
               $user->hasPermission('manage_users') && 
               $user->level > $model->level;
    }

    public function impersonate(User $user, User $model): bool
    {
        return $user->hasPermission('impersonate_users') && 
               $user->level > $model->level;
    }
}