<?php
namespace App\Policies;

use App\Models\SaleInvoice;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View Users');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create Users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('Edit Users');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('Delete Users') && $user->id !== $model->id;
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('View Users');
    }
    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('Delete Users');
    }
}
