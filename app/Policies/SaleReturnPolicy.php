<?php

namespace App\Policies;

use App\Models\SaleReturn;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SaleReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_salereturns');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_salereturns');
    }

    public function update(User $user, SaleReturn $model): bool
    {
        return $user->hasPermission('edit_salereturns');
    }

    public function delete(User $user, SaleReturn $model): bool
    {
        return $user->hasPermission('delete_salereturns');
    }

    public function view(User $user, SaleReturn $model): bool
    {
        return $user->hasPermission('view_salereturns');
    }
}
