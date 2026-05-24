<?php

namespace App\Events;

use App\Models\CourseChatMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param 'group'|'direct' $threadType
     */
    public function __construct(
        public CourseChatMessage $message,
        public string $threadType,
        public int $courseId,
        /** For 'direct' threads: the student that owns the direct chat row. Null for group. */
        public ?int $directStudentId = null,
    ) {
    }
}
