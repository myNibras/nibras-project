<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `coupons` CHANGE `number_of_coupons` `limit_usage` INTEGER NULL DEFAULT 0');
        } else {
            // For PostgreSQL
            Schema::table('coupons', function (Blueprint $table) {
                $table->renameColumn('number_of_coupons', 'limit_usage');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `coupons` CHANGE `limit_usage` `number_of_coupons` INTEGER NULL DEFAULT 0');
        } else {
            // For PostgreSQL
            Schema::table('coupons', function (Blueprint $table) {
                $table->renameColumn('limit_usage', 'number_of_coupons');
            });
        }
    }
};
