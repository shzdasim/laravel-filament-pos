<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_customers');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_customers');
    }

    public function update(User $user, Customer $model): bool
    {
        return $user->hasPermission('edit_customers');
    }

    public function delete(User $user, Customer $model): bool
    {
        return $user->hasPermission('delete_customers');
    }

    public function view(User $user, Customer $model): bool
    {
        return $user->hasPermission('view_customers');
    }
}
