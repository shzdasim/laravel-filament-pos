<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View Products');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create Products');
    }

    public function update(User $user, Product $model): bool
    {
        return $user->hasPermission('Edit Products');
    }

    public function delete(User $user, Product $model): bool
    {
        return $user->hasPermission('Delete Products');
    }

    public function view(User $user, Product $model): bool
    {
        return $user->hasPermission('View Products');
    }
    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('Delete Products');
    }
}
