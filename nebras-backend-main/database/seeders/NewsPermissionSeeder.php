<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class NewsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = ['view', 'create', 'edit', 'delete'];
        $module = 'news';

        // Create news permissions
        foreach ($actions as $action) {
            $permissionName = "{$action} {$module}";
            
            // Check if permission already exists
            $permission = Permission::where('name', $permissionName)->first();
            
            if (!$permission) {
                Permission::create(['name' => $permissionName]);
                $this->command->info("Created permission: {$permissionName}");
            } else {
                $this->command->info("Permission already exists: {$permissionName}");
            }
        }

        // Assign all permissions to Super Admin role
        $superAdmin = Role::where('name_en', 'super admin')->first();
        if ($superAdmin) {
            $newsPermissions = Permission::where('name', 'like', "%{$module}%")->get();
            $superAdmin->givePermissionTo($newsPermissions);
            $this->command->info("Assigned news permissions to Super Admin role");
        }

        $this->command->info('News permissions seeded successfully!');
    }
}

