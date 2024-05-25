<?php

namespace App\Policies;

use App\Models\PurchaseInvoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchaseInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_purchaseinvoices');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_purchaseinvoices');
    }

    public function update(User $user, PurchaseInvoice $model): bool
    {
        return $user->hasPermission('edit_purchaseinvoices');
    }

    public function delete(User $user, PurchaseInvoice $model): bool
    {
        return $user->hasPermission('delete_purchaseinvoices');
    }

    public function view(User $user, PurchaseInvoice $model): bool
    {
        return $user->hasPermission('view_purchaseinvoices');
    }
}
