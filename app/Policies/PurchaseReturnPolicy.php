<?php

namespace App\Policies;

use App\Models\PurchaseReturn;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchaseReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View PurchaseReturns');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create PurchaseReturns');
    }

    public function update(User $user, PurchaseReturn $model): bool
    {
        return $user->hasPermission('Edit PurchaseReturns');
    }

    public function delete(User $user, PurchaseReturn $model): bool
    {
        return $user->hasPermission('Delete PurchaseReturns');
    }

    public function view(User $user, PurchaseReturn $model): bool
    {
        return $user->hasPermission('View PurchaseReturns');
    }
    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('Delete PurchaseReturns');
    }

}
