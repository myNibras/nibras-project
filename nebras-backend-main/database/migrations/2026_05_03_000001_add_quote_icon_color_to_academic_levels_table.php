<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('academic_levels', function (Blueprint $table) {
            $table->string('quote_icon_color', 7)->default('#1396FD')->after('slug_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_levels', function (Blueprint $table) {
            $table->dropColumn('quote_icon_color');
        });
    }
};
