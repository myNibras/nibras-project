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
        Schema::table('payment_items', function (Blueprint $table) {
            $table->text('short_description')->change();
            $table->text('short_description_en')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_items', function (Blueprint $table) {
            $table->string('short_description')->change();
            $table->string('short_description_en')->change();
        });
    }
};
