<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Delete all existing permissions and roles
        Permission::truncate();
        Role::truncate();

        $modules = [
            'home slider',
            'students',
            'classes',
            'academic level',
            'courses',
            'payments',
            'semesters',
            'teachers',
            'admins',
            'roles',
            'testimonials',
            'coupons',
            'faqs'
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        // Create all permissions
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::create(['name' => "{$action} {$module}"]);
            }
        }

        // Create roles
        $superAdmin = Role::create(['name' => 'المشرف الأعلى', 'name_en' => 'super admin']);
        $admin      = Role::create(['name' => 'المشرف', 'name_en' => 'admin']);
        $finance    = Role::create(['name' => 'الدعم', 'name_en' => 'finance']);
        $support    = Role::create(['name' => 'المالية', 'name_en' => 'support']);

        // Assign all permissions to Super Admin
        $superAdmin->givePermissionTo(Permission::all());

        // Assign Super Admin role to first admin
        $adminUser = Admin::find(1);
        if ($adminUser) {
            $adminUser->assignRole($superAdmin);
        }
    }
}
