<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        $permissions = ['create', 'read', 'update', 'delete'];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole->permissions()->sync(Permission::pluck('id')->all());
    }
}
