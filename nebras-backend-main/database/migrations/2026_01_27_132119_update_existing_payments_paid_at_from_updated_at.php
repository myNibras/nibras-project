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
        // Update existing successful payments to set paid_at from updated_at
        DB::table('payments')
            ->where('status', 'success')
            ->whereNull('paid_at')
            ->update([
                'paid_at' => DB::raw('updated_at')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set paid_at to null for payments that were updated by this migration
        // Note: This will clear paid_at for all successful payments, not just the ones we updated
        // A more precise rollback would require tracking which records were updated
        DB::table('payments')
            ->where('status', 'success')
            ->whereColumn('paid_at', 'updated_at')
            ->update([
                'paid_at' => null
            ]);
    }
};
