<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\User;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        // Define permissions
        $permissions = [
            'Create Application',
            'Edit Application',
            'Delete Application',
            'View Application',
            'Create Users',
            'Edit Users',
            'Delete Users',
            'View Users',
            'Create SaleInvoices',
            'Edit SaleInvoices',
            'Delete SaleInvoices',
            'View SaleInvoices',
            'Create PurchaseInvoices',
            'Edit PurchaseInvoices',
            'Delete PurchaseInvoices',
            'View PurchaseInvoices',
            'Create Products',
            'Edit Products',
            'Delete Products',
            'View Products',
            'Create Categories',
            'Edit Categories',
            'Delete Categories',
            'View Categories',
            'Create Suppliers',
            'Edit Suppliers',
            'Delete Suppliers',
            'View Suppliers',
            'Create Customers',
            'Edit Customers',
            'Delete Customers',
            'View Customers',
            'Create SaleReturns',
            'Edit SaleReturns',
            'Delete SaleReturns',
            'View SaleReturns',
            'Create PurchaseReturns',
            'Edit PurchaseReturns',
            'Delete PurchaseReturns',
            'View PurchaseReturns',

        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign all permissions to the admin user
        $admin = User::where('email', 'admin@example.com')->first(); // Replace with your admin user's email
        if ($admin) {
            $admin->permissions()->attach(Permission::all());
        }
    }
}
