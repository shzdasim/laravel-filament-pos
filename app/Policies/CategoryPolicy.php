<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_categories');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_categories');
    }

    public function update(User $user, Category $model): bool
    {
        return $user->hasPermission('edit_categories');
    }

    public function delete(User $user, Category $model): bool
    {
        return $user->hasPermission('delete_categories');
    }

    public function view(User $user, Category $model): bool
    {
        return $user->hasPermission('view_categories');
    }
}
