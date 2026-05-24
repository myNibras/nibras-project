<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            TestimonialsPermissionSeeder::class,
            PartnersPermissionSeeder::class,
            FaqsPermissionSeeder::class,
            ContactSubmissionsPermissionSeeder::class,
            CandidatesPermissionSeeder::class,
            SettingsPermissionSeeder::class,
        ]);
    }
}
