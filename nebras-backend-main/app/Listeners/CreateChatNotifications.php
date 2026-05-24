<?php

namespace App\Listeners;

use App\Events\ChatMessageSent;
use App\Models\ChatNotification;
use App\Models\Course;
use App\Models\CourseChatMessage;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Log;

class CreateChatNotifications
{
    public function handle(ChatMessageSent $event): void
    {
        try {
            $message = $event->message;
            $course = Course::find($event->courseId);
            if (!$course) {
                return;
            }

            $senderType = $message->sender_type === CourseChatMessage::SENDER_TEACHER
                ? Teacher::class
                : Student::class;
            $senderId = (int) $message->sender_id;

            $recipients = match ($event->threadType) {
                'group' => $this->groupRecipients($course, $senderType, $senderId),
                'direct' => $this->directRecipients($course, $senderType, $senderId, $event->directStudentId),
                default => [],
            };

            foreach ($recipients as $r) {
                ChatNotification::create([
                    'recipient_type' => $r['type'],
                    'recipient_id' => $r['id'],
                    'course_id' => $course->id,
                    'thread_type' => $event->threadType,
                    'thread_partner_type' => $r['partner_type'] ?? null,
                    'thread_partner_id' => $r['partner_id'] ?? null,
                    'chat_message_id' => $message->id,
                    'is_read' => false,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('CreateChatNotifications failed', ['exception' => $e]);
        }
    }

    /** @return array<int, array{type: class-string, id: int, partner_type?: class-string, partner_id?: int}> */
    private function groupRecipients(Course $course, string $senderType, int $senderId): array
    {
        $recipients = [];

        if ($course->teacher_id && !($senderType === Teacher::class && $senderId === (int) $course->teacher_id)) {
            $recipients[] = ['type' => Teacher::class, 'id' => (int) $course->teacher_id];
        }

        $studentIds = PaymentItem::where('course_id', $course->id)
            ->whereHas('payment', fn ($q) => $q->where('status', 'success'))
            ->with('payment:id,student_id')
            ->get()
            ->map(fn ($i) => $i->payment?->student_id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        foreach ($studentIds as $sid) {
            if ($senderType === Student::class && (int) $sid === $senderId) {
                continue;
            }
            $recipients[] = ['type' => Student::class, 'id' => (int) $sid];
        }

        return $recipients;
    }

    /** @return array<int, array{type: class-string, id: int, partner_type: class-string, partner_id: int}> */
    private function directRecipients(Course $course, string $senderType, int $senderId, ?int $directStudentId): array
    {
        if (!$directStudentId || !$course->teacher_id) {
            return [];
        }

        if ($senderType === Teacher::class) {
            return [[
                'type' => Student::class,
                'id' => $directStudentId,
                'partner_type' => Teacher::class,
                'partner_id' => (int) $course->teacher_id,
            ]];
        }

        return [[
            'type' => Teacher::class,
            'id' => (int) $course->teacher_id,
            'partner_type' => Student::class,
            'partner_id' => $directStudentId,
        ]];
    }
}
