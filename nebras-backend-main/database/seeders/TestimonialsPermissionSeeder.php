<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TestimonialsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = ['view', 'create', 'edit', 'delete'];
        $module = 'testimonials';

        // Create testimonials permissions
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
            $testimonialsPermissions = Permission::where('name', 'like', "%{$module}%")->get();
            $superAdmin->givePermissionTo($testimonialsPermissions);
            $this->command->info("Assigned testimonials permissions to Super Admin role");
        }

        $this->command->info('Testimonials permissions seeded successfully!');
    }
}
