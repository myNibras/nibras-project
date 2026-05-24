<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FaqsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = ['view', 'create', 'edit', 'delete'];
        $module = 'faqs';

        foreach ($actions as $action) {
            $permissionName = "{$action} {$module}";

            $permission = Permission::where('name', $permissionName)->first();

            if (!$permission) {
                Permission::create(['name' => $permissionName]);
                $this->command->info("Created permission: {$permissionName}");
            } else {
                $this->command->info("Permission already exists: {$permissionName}");
            }
        }

        $superAdmin = Role::where('name_en', 'super admin')->first();
        if ($superAdmin) {
            $faqsPermissions = Permission::where('name', 'like', "%{$module}%")->get();
            $superAdmin->givePermissionTo($faqsPermissions);
            $this->command->info('Assigned FAQs permissions to Super Admin role');
        }

        $this->command->info('FAQs permissions seeded successfully!');
    }
}
