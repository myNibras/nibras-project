<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseChat extends Model
{
    protected $fillable = ['course_id', 'student_id'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CourseChatMessage::class)->orderBy('created_at');
    }

    /** Whether this is the course-wide chat (one-to-many). */
    public function isChannel(): bool
    {
        return $this->student_id === null;
    }

    /** Whether this is a direct thread (one-to-one with a student). */
    public function isDirect(): bool
    {
        return $this->student_id !== null;
    }
}
