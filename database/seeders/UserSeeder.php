<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $adminRole = Role::create(['name' => 'admin']);
        }

        $userRole = Role::where('name', 'user')->first();
        if (!$userRole) {
            $userRole = Role::create(['name' => 'user']);
        }

        $permissions = [
            ['name' => 'Dashboard', 'slug' => 'Dashboard', 'groupby' => 1],

            
            ['name' => 'Admin', 'slug' => 'Admin', 'groupby' => 2],

          
            ['name' => 'User', 'slug' => 'User', 'groupby' => 3],
            ['name' => 'Add-user', 'slug' => 'Add-user', 'groupby' => 3],
            ['name' => 'View-user', 'slug' => 'View-user', 'groupby' => 3],
            ['name' => 'Edit-user', 'slug' => 'Edit-user', 'groupby' => 3],
            ['name' => 'Delete-user', 'slug' => 'Delete-user', 'groupby' => 3],

            
            ['name' => 'Role', 'slug' => 'Role', 'groupby' => 4],
            ['name' => 'Add-role', 'slug' => 'Add-role', 'groupby' => 4],
            ['name' => 'View-role', 'slug' => 'View-role', 'groupby' => 4],
            ['name' => 'Edit-role', 'slug' => 'Edit-role', 'groupby' => 4],
            ['name' => 'Delete-role', 'slug' => 'Delete-role', 'groupby' => 4],

          
            ['name' => 'Account', 'slug' => 'Account', 'groupby' => 5],
            ['name' => 'Add-account', 'slug' => 'Add-account', 'groupby' => 5],
            ['name' => 'View-account', 'slug' => 'View-account', 'groupby' => 5],
            ['name' => 'Edit-account', 'slug' => 'Edit-account', 'groupby' => 5],
            ['name' => 'Delete-account', 'slug' => 'Delete-account', 'groupby' => 5],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'slug' => $permission['slug'],
                'groupby' => $permission['groupby']
            ]);
        }

        $adminRole->permissions()->sync(Permission::pluck('id')->all());

        $adminUser = User::firstOrCreate([
            'email' => 'admin1@gmail.com',
        ], [
            'id' => Str::uuid(),
            'name' => 'Admin',
            'password' => Hash::make('12345678'),
        ]);

        $adminUser->roles()->sync([$adminRole->id]);

        $user = User::firstOrCreate([
            'email' => 'user@gmail.com',
        ], [
            'id' => Str::uuid(),
            'name' => 'User',
            'password' => Hash::make('12345678'),
        ]);

        $user->roles()->sync([$userRole->id]);
    }
}
