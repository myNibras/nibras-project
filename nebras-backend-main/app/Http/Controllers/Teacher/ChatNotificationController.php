<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ChatNotification;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatNotificationController extends Controller
{
    public function unreadCount(): JsonResponse
    {
        $teacher = Auth::guard('teacher')->user();
        $count = ChatNotification::query()
            ->where('recipient_type', Teacher::class)
            ->where('recipient_id', $teacher->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['status' => true, 'data' => ['count' => $count]]);
    }

    public function index(): JsonResponse
    {
        $teacher = Auth::guard('teacher')->user();
        $rows = ChatNotification::query()
            ->with(['chatMessage', 'course'])
            ->where('recipient_type', Teacher::class)
            ->where('recipient_id', $teacher->id)
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $messages = $rows->pluck('chatMessage')->filter();
        $teacherIds = $messages
            ->where('sender_type', \App\Models\CourseChatMessage::SENDER_TEACHER)
            ->pluck('sender_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $studentIds = $messages
            ->where('sender_type', \App\Models\CourseChatMessage::SENDER_STUDENT)
            ->pluck('sender_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $teachers = $teacherIds
            ? \App\Models\Teacher::whereIn('id', $teacherIds)->get(['id', 'name', 'name_en'])->keyBy('id')
            : collect();
        $students = $studentIds
            ? \App\Models\Student::whereIn('id', $studentIds)->get(['id', 'name'])->keyBy('id')
            : collect();

        $data = $rows->map(function (ChatNotification $n) use ($teachers, $students) {
            $msg = $n->chatMessage;
            $senderName = null;
            if ($msg) {
                if ($msg->sender_type === \App\Models\CourseChatMessage::SENDER_TEACHER) {
                    $t = $teachers->get($msg->sender_id);
                    $senderName = $t ? $t->getLocalizationName() : null;
                } else {
                    $s = $students->get($msg->sender_id);
                    $senderName = $s?->name;
                }
            }
            return [
                'id' => $n->id,
                'course_id' => $n->course_id,
                'course_name' => $n->course?->title,
                'course_slug' => $n->course?->slug,
                'course_slug_en' => $n->course?->slug_en,
                'thread_type' => $n->thread_type,
                'thread_partner_type' => $n->thread_partner_type ? class_basename($n->thread_partner_type) : null,
                'thread_partner_id' => $n->thread_partner_id,
                'sender_name' => $senderName,
                'body_preview' => Str::limit($msg?->body ?? '', 80),
                'created_at' => $n->created_at?->toIso8601String(),
            ];
        });

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'thread_type' => 'required|in:group,direct',
            'thread_partner_id' => 'nullable|integer',
        ]);

        $teacher = Auth::guard('teacher')->user();

        $query = ChatNotification::query()
            ->where('recipient_type', Teacher::class)
            ->where('recipient_id', $teacher->id)
            ->where('course_id', $request->integer('course_id'))
            ->where('thread_type', (string) $request->string('thread_type'))
            ->where('is_read', false);

        if ((string) $request->string('thread_type') === 'direct' && $request->filled('thread_partner_id')) {
            $query->where('thread_partner_id', $request->integer('thread_partner_id'));
        }

        $marked = $query->update(['is_read' => true]);

        return response()->json(['status' => true, 'data' => ['marked' => $marked]]);
    }
}
