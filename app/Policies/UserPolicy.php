<?php
namespace App\Policies;

use App\Models\SaleInvoice;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_users');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('edit_users');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('delete_users') && $user->id !== $model->id;
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('view_users');
    }
}
