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
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('payment_type', ['one-off', 'monthly', 'both'])->default('one-off')->after('discount_price');
            $table->integer('semester_months')->nullable()->after('payment_type');
            $table->decimal('monthly_amount', 10, 2)->nullable()->after('semester_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'semester_months', 'monthly_amount']);
        });
    }
};
