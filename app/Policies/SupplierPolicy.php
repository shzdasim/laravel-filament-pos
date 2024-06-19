<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View Suppliers');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create Suppliers');
    }

    public function update(User $user, Supplier $model): bool
    {
        return $user->hasPermission('Edit Suppliers');
    }

    public function delete(User $user, Supplier $model): bool
    {
        return $user->hasPermission('Delete Suppliers');
    }

    public function view(User $user, Supplier $model): bool
    {
        return $user->hasPermission('View Suppliers');
    }
    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('Delete Suppliers');
    }
}
