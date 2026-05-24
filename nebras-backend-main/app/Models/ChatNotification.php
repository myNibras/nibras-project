<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChatNotification extends Model
{
    protected $fillable = [
        'recipient_type',
        'recipient_id',
        'course_id',
        'thread_type',
        'thread_partner_type',
        'thread_partner_id',
        'chat_message_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    public function threadPartner(): MorphTo
    {
        return $this->morphTo();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(CourseChatMessage::class);
    }
}
