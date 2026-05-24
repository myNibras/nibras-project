<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('label_en');
            $table->boolean('value')->default(true);
            $table->timestamps();
        });

        // Seed initial setting: Show Students number in teacher
        DB::table('settings')->insert([
            'key' => 'show_students_number_in_teacher',
            'label' => 'عرض عدد الطلاب في المعلم',
            'label_en' => 'Show Students number in teacher',
            'value' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

