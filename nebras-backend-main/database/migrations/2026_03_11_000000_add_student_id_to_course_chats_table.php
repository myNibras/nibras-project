<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * When student_id is null = course-wide chat (one-to-many: teacher + all students).
     * When student_id is set = direct thread (one-to-one: teacher + that student).
     */
    public function up(): void
    {
        Schema::table('course_chats', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('course_id')->constrained('students')->cascadeOnDelete();
            $table->unique(['course_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_chats', function (Blueprint $table) {
            $table->dropUnique(['course_id', 'student_id']);
            $table->dropForeign(['student_id']);
        });
    }
};
