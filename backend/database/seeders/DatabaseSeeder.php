<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage-services',
            'manage-deals',
            'manage-staff',
            'create-bill',
            'view-reports',
            'add-expense',
            'view-own-commission',
            'manage-users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->syncPermissions($permissions);

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions(['manage-staff', 'create-bill', 'view-reports', 'add-expense', 'view-own-commission']);

        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->syncPermissions(['create-bill', 'view-own-commission']);

        $user = User::firstOrCreate(
            ['email' => 'owner@salon.com'],
            ['name' => 'Owner', 'password' => bcrypt('password')]
        );
        $user->assignRole('owner');

        $this->call(DemoDataSeeder::class);
    }
}
