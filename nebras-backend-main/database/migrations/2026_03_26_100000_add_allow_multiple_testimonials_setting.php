<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::query()->firstOrCreate(
            ['key' => 'allow_multiple_testimonials'],
            [
                'label' => 'السماح بتقديم أكثر من شهادة لنفس المادة',
                'label_en' => 'Allow multiple testimonials per course (same student)',
                'value' => false,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::query()->where('key', 'allow_multiple_testimonials')->delete();
    }
};
