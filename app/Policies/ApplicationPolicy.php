<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Application;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_applications');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_applications');
    }

    public function update(User $user, Application $model): bool
    {
        return $user->hasPermission('edit_applications');
    }

    public function delete(User $user, Application $model): bool
    {
        return $user->hasPermission('delete_applications');
    }

    public function view(User $user, Application $model): bool
    {
        return $user->hasPermission('view_applications');
    }
}