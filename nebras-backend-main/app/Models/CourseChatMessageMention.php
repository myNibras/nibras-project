<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseChatMessageMention extends Model
{
    protected $fillable = [
        'course_chat_message_id',
        'student_id',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(CourseChatMessage::class, 'course_chat_message_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
