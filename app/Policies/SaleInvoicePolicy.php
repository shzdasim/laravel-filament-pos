<?php

namespace App\Policies;

use App\Models\SaleInvoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SaleInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_saleinvoices');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_saleinvoices');
    }

    public function update(User $user, SaleInvoice $model): bool
    {
        return $user->hasPermission('edit_saleinvoices');
    }

    public function delete(User $user, SaleInvoice $model): bool
    {
        return $user->hasPermission('delete_saleinvoices');
    }

    public function view(User $user, SaleInvoice $model): bool
    {
        return $user->hasPermission('view_saleinvoices');
    }
}
