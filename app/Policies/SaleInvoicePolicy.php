<?php

namespace App\Policies;

use App\Models\SaleInvoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SaleInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View SaleInvoices');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create SaleInvoices');
    }

    public function update(User $user, SaleInvoice $model): bool
    {
        return $user->hasPermission('Edit SaleInvoices');
    }

    public function delete(User $user, SaleInvoice $model): bool
    {
        return $user->hasPermission('Delete SaleInvoices');
    }

    public function view(User $user, SaleInvoice $model): bool
    {
        return $user->hasPermission('View SaleInvoices');
    }
    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('Delete SaleInvoices');
    }
}
