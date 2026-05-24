<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_notifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('recipient');
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->enum('thread_type', ['group', 'direct']);
            $table->nullableMorphs('thread_partner');
            $table->foreignId('chat_message_id')
                ->constrained('course_chat_messages')
                ->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(
                ['recipient_type', 'recipient_id', 'is_read'],
                'chat_notif_recipient_unread_idx'
            );
            $table->index(
                ['recipient_type', 'recipient_id', 'course_id', 'thread_type', 'is_read'],
                'chat_notif_thread_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_notifications');
    }
};
