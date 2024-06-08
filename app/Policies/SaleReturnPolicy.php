<?php

namespace App\Policies;

use App\Models\SaleReturn;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SaleReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View SaleReturns');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create SaleReturns');
    }

    public function update(User $user, SaleReturn $model): bool
    {
        return $user->hasPermission('Edit SaleReturns');
    }

    public function delete(User $user, SaleReturn $model): bool
    {
        return $user->hasPermission('Delete SaleReturns');
    }

    public function view(User $user, SaleReturn $model): bool
    {
        return $user->hasPermission('View SaleReturns');
    }
    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('Delete SaleReturns');
    }
}
