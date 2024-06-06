<?php

namespace App\Policies;

use App\Models\PurchaseReturn;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchaseReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View PurchaseReturn');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create PurchaseReturn');
    }

    public function update(User $user, PurchaseReturn $model): bool
    {
        return $user->hasPermission('Edit PurchaseReturn');
    }

    public function delete(User $user, PurchaseReturn $model): bool
    {
        return $user->hasPermission('Delete PurchaseReturn');
    }

    public function view(User $user, PurchaseReturn $model): bool
    {
        return $user->hasPermission('View PurchaseReturn');
    }
    public function deleteAny(User $user): bool
{
    return $user->hasPermission('Delete PurchaseReturn');
}

}
