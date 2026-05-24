<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PositionsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = ['view', 'create', 'edit', 'delete'];
        $module = 'positions';

        foreach ($actions as $action) {
            $permissionName = "{$action} {$module}";

            if (!Permission::where('name', $permissionName)->exists()) {
                Permission::create(['name' => $permissionName]);
                $this->command->info("Created permission: {$permissionName}");
            } else {
                $this->command->info("Permission already exists: {$permissionName}");
            }
        }

        $superAdmin = Role::where('name_en', 'super admin')->first();
        if ($superAdmin) {
            $positionsPermissions = Permission::where('name', 'like', "%{$module}%")->get();
            $superAdmin->givePermissionTo($positionsPermissions);
            $this->command->info('Assigned positions permissions to Super Admin role');
        }

        $this->command->info('Positions permissions seeded successfully!');
    }
}
