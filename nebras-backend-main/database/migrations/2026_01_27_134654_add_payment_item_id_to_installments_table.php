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
        Schema::table('installments', function (Blueprint $table) {
            $table->foreignId('payment_item_id')->nullable()->after('payment_id')->constrained('payment_items')->cascadeOnDelete();
            $table->index(['payment_item_id', 'installment_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->dropForeign(['payment_item_id']);
            $table->dropIndex(['payment_item_id', 'installment_number']);
            $table->dropColumn('payment_item_id');
        });
    }
};
