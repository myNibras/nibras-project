<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'students_testimonials' to the allowed enum values for type
        DB::statement("
            ALTER TABLE additional_information
            MODIFY COLUMN type ENUM(
                'partners',
                'coupon',
                'academic_level',
                'faq',
                'article',
                'news',
                'teachers',
                'students_testimonials'
            ) NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum values (without students_testimonials)
        DB::statement("
            ALTER TABLE additional_information
            MODIFY COLUMN type ENUM(
                'partners',
                'coupon',
                'academic_level',
                'faq',
                'article',
                'news',
                'teachers'
            ) NULL
        ");
    }
};

