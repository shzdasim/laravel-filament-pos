<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_products');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_products');
    }

    public function update(User $user, Product $model): bool
    {
        return $user->hasPermission('update_products');
    }

    public function delete(User $user, Product $model): bool
    {
        return $user->hasPermission('delete_products');
    }

    public function view(User $user, Product $model): bool
    {
        return $user->hasPermission('view_products');
    }
}
