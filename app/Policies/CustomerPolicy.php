<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View Customers');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create Customers');
    }

    public function update(User $user, Customer $model): bool
    {
        return $user->hasPermission('Edit Customers');
    }

    public function delete(User $user, Customer $model): bool
    {
        return $user->hasPermission('Delete Customers');
    }

    public function view(User $user, Customer $model): bool
    {
        return $user->hasPermission('View Customers');
    }
    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('Delete Customers');
    }
}
