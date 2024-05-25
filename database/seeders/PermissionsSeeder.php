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
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_applications',
            'create_applications',
            'edit_applications',
            'delete_applications',
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            'create_categories',
            'view_categories',
            'edit_categories',
            'delete_categories',
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
            'view_saleinvoices',
            'create_saleinvoices',
            'edit_saleinvoices',
            'delete_saleinvoices',
            'view_purchaseinvoices',
            'create_purchaseinvoices',
            'edit_purchaseinvoices',
            'delete_purchaseinvoices',

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
