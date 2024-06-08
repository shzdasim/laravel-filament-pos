<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Application;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View Application');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create Application');
    }

    public function update(User $user, Application $model): bool
    {
        return $user->hasPermission('Edit Application');
    }

    public function delete(User $user, Application $model): bool
    {
        return $user->hasPermission('Delete Application');
    }

    public function view(User $user, Application $model): bool
    {
        return $user->hasPermission('View Application');
    }
}