<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View Categories');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create Categories');
    }

    public function update(User $user, Category $model): bool
    {
        return $user->hasPermission('Edit Categories');
    }

    public function delete(User $user, Category $model): bool
    {
        return $user->hasPermission('Delete Categories');
    }

    public function view(User $user, Category $model): bool
    {
        return $user->hasPermission('View Categories');
    }
    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('Delete Categories');
    }
}
