<?php

namespace App\Policies;

use App\Models\PurchaseInvoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchaseInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('View PurchaseInvoices');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('Create PurchaseInvoices');
    }

    public function update(User $user, PurchaseInvoice $model): bool
    {
        return $user->hasPermission('Edit PurchaseInvoices');
    }

    public function delete(User $user, PurchaseInvoice $model): bool
    {
        return $user->hasPermission('Delete PurchaseInvoices');
    }

    public function view(User $user, PurchaseInvoice $model): bool
    {
        return $user->hasPermission('View PurchaseInvoices');
    }
    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('Delete PurchaseInvoices');
    }
}
