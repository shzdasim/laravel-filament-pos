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
