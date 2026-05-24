<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_chat_message_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_chat_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['course_chat_message_id', 'student_id'], 'chat_message_mention_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_chat_message_mentions');
    }
};
