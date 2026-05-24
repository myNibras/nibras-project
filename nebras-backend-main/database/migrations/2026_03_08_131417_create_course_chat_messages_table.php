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
        Schema::create('course_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_chat_id')->constrained()->cascadeOnDelete();
            $table->string('sender_type', 20); // 'student' | 'teacher'
            $table->unsignedBigInteger('sender_id');
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_chat_messages');
    }
};
