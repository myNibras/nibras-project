<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseChatMessage extends Model
{
    public const SENDER_STUDENT = 'student';
    public const SENDER_TEACHER = 'teacher';

    protected $fillable = [
        'course_chat_id',
        'sender_type',
        'sender_id',
        'reply_to_message_id',
        'body',
    ];

    public function courseChat(): BelongsTo
    {
        return $this->belongsTo(CourseChat::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_message_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CourseChatMessageLike::class);
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(CourseChatMessageMention::class);
    }

    /**
     * Get the sender model (Student or Teacher) based on sender_type.
     */
    public function getSenderNameAttribute(): string
    {
        if ($this->sender_type === self::SENDER_TEACHER) {
            $teacher = Teacher::find($this->sender_id);
            return $teacher ? $teacher->getLocalizationName() : __('Unknown');
        }
        $student = Student::find($this->sender_id);
        return $student ? $student->name : __('Unknown');
    }
}
