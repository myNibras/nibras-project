<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CandidatesPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = ['view', 'delete'];
        $module = 'candidates';

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
            $candidatesPermissions = Permission::where('name', 'like', "%{$module}%")->get();
            $superAdmin->givePermissionTo($candidatesPermissions);
            $this->command->info('Assigned candidates permissions to Super Admin role');
        }

        $this->command->info('Candidates permissions seeded successfully!');
    }
}
