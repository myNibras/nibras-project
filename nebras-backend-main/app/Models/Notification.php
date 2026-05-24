<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    /**
     * Avoid clashing with Laravel's default `notifications` table for database notifications.
     */
    protected $table = 'notification_messages';

    protected $fillable = [
        'student_id',
        'message_ar',
        'message_en',
        'is_read',
        'model_type',
        'model_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Related subject (polymorphic), e.g. course, payment.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Localized message for the current app locale.
     */
    public function getLocalizedMessage(): string
    {
        return app()->getLocale() === 'ar' ? $this->message_ar : $this->message_en;
    }
}
