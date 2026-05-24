<?php

namespace App\Http\Controllers\Teacher;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseChat;
use App\Models\CourseChatMessage;
use App\Models\PaymentItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    private function formatMessage(CourseChatMessage $msg): array
    {
        return [
            'id' => $msg->id,
            'sender_type' => $msg->sender_type,
            'sender_id' => $msg->sender_id,
            'sender_name' => $msg->sender_name,
            'body' => $msg->body,
            'reply_to_message_id' => $msg->reply_to_message_id,
            'reply_to' => $msg->replyTo ? [
                'id' => $msg->replyTo->id,
                'sender_name' => $msg->replyTo->sender_name,
                'body' => $msg->replyTo->body,
            ] : null,
            'likes_count' => (int) ($msg->likes_count ?? 0),
            'mentioned_students' => $msg->mentions->map(fn ($mention) => [
                'id' => $mention->student_id,
                'name' => $mention->student?->name,
            ])->values(),
            'created_at' => $msg->created_at?->toIso8601String(),
        ];
    }

    private function resolveReplyMessage(CourseChat $courseChat, ?int $replyToMessageId): ?CourseChatMessage
    {
        if (!$replyToMessageId) {
            return null;
        }

        return $courseChat->messages()->whereKey($replyToMessageId)->first();
    }

    private function enrolledStudentIdsForCourse(int $courseId): array
    {
        return PaymentItem::where('course_id', $courseId)
            ->whereHas('payment', fn ($q) => $q->where('status', 'success'))
            ->with('payment:id,student_id')
            ->get()
            ->map(fn ($item) => $item->payment?->student_id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function findCourse(int $id): Course
    {
        return Course::where('id', $id)
            ->where('teacher_id', Auth::guard('teacher')->id())
            ->firstOrFail();
    }

    /**
     * @param int|null $studentId null = course-wide (one-to-many), set = direct (one-to-one)
     */
    private function getOrCreateCourseChat(Course $course, ?int $studentId = null): CourseChat
    {
        return CourseChat::firstOrCreate(
            [
                'course_id' => $course->id,
                'student_id' => $studentId,
            ],
            [
                'course_id' => $course->id,
                'student_id' => $studentId,
            ]
        );
    }

    /**
     * Show the teacher chat page with course list.
     */
    public function index(): View
    {
        $teacher = Auth::guard('teacher')->user();
        $courses = $teacher->courses()
            ->with(['academicLevel', 'semester', 'classRoom'])
            ->orderBy('title')
            ->get();

        return view('teacher.chat', compact('teacher', 'courses'));
    }

    /**
     * List enrolled students for a course (for direct chat selector). Teacher must own the course.
     */
    public function getEnrolledStudents(int $courseId): JsonResponse
    {
        $course = $this->findCourse($courseId);
        $studentIds = PaymentItem::where('course_id', $course->id)
            ->whereHas('payment', fn ($q) => $q->where('status', 'success'))
            ->with('payment:id,student_id')
            ->get()
            ->map(fn ($item) => $item->payment?->student_id)
            ->filter()
            ->unique()
            ->values();
        $students = \App\Models\Student::whereIn('id', $studentIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'email' => $s->email]);

        return response()->json([
            'status' => true,
            'data' => $students,
        ]);
    }

    /**
     * Get messages for course chat (channel = one-to-many). Teacher must own the course.
     */
    public function getMessages(Request $request, int $courseId): JsonResponse
    {
        $course = $this->findCourse($courseId);
        $courseChat = $this->getOrCreateCourseChat($course, null);

        $messages = $courseChat->messages()
            ->with(['replyTo', 'mentions.student'])
            ->withCount('likes')
            ->orderBy('created_at')
            ->get()
            ->map(fn (CourseChatMessage $msg) => $this->formatMessage($msg));

        return response()->json([
            'status' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Send a message (AJAX). Teacher must own the course.
     */
    public function sendMessage(Request $request, int $courseId): JsonResponse
    {
        $request->validate([
            'body' => 'required|string|max:4000',
            'reply_to_message_id' => 'nullable|integer|exists:course_chat_messages,id',
            'mentioned_student_ids' => 'nullable|array',
            'mentioned_student_ids.*' => 'integer|exists:students,id',
        ]);

        $teacher = Auth::guard('teacher')->user();
        $course = $this->findCourse($courseId);
        $courseChat = $this->getOrCreateCourseChat($course);
        $mentionedIds = collect($request->input('mentioned_student_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $invalidMention = $mentionedIds->first(
            fn ($id) => !in_array($id, $this->enrolledStudentIdsForCourse($courseId), true)
        );
        if ($invalidMention) {
            return response()->json([
                'status' => false,
                'message' => 'Mention target not enrolled in this course group chat',
            ], 422);
        }
        $replyTo = $this->resolveReplyMessage($courseChat, $request->integer('reply_to_message_id'));
        if ($request->filled('reply_to_message_id') && !$replyTo) {
            return response()->json([
                'status' => false,
                'message' => 'Reply target not found in this chat thread',
            ], 422);
        }

        $message = $courseChat->messages()->create([
            'sender_type' => CourseChatMessage::SENDER_TEACHER,
            'sender_id' => $teacher->id,
            'reply_to_message_id' => $replyTo?->id,
            'body' => $request->body,
        ]);
        if ($mentionedIds->isNotEmpty()) {
            $message->mentions()->createMany(
                $mentionedIds->map(fn ($id) => ['student_id' => $id])->all()
            );
        }
        $message->load(['replyTo', 'mentions.student'])->loadCount('likes');

        event(new ChatMessageSent($message, 'group', $course->id));

        return response()->json([
            'status' => true,
            'message' => __('messages.message_sent'),
            'data' => $this->formatMessage($message),
        ], 201);
    }

    /**
     * Get messages for direct (one-to-one) chat with a student. Teacher must own the course.
     */
    public function getDirectMessages(Request $request, int $courseId, int $studentId): JsonResponse
    {
        $course = $this->findCourse($courseId);
        $this->ensureStudentEnrolledInCourse($courseId, $studentId);
        $courseChat = $this->getOrCreateCourseChat($course, $studentId);

        $messages = $courseChat->messages()
            ->with(['replyTo', 'mentions.student'])
            ->withCount('likes')
            ->orderBy('created_at')
            ->get()
            ->map(fn (CourseChatMessage $msg) => $this->formatMessage($msg));

        return response()->json([
            'status' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Send a message in direct (one-to-one) chat with a student. Teacher must own the course.
     */
    public function sendDirectMessage(Request $request, int $courseId, int $studentId): JsonResponse
    {
        $request->validate([
            'body' => 'required|string|max:4000',
            'reply_to_message_id' => 'nullable|integer|exists:course_chat_messages,id',
        ]);
        if (! empty(array_filter((array) $request->input('mentioned_student_ids', [])))) {
            return response()->json([
                'status' => false,
                'message' => 'Mentions are allowed only in group chat',
            ], 422);
        }

        $teacher = Auth::guard('teacher')->user();
        $course = $this->findCourse($courseId);
        $this->ensureStudentEnrolledInCourse($courseId, $studentId);
        $courseChat = $this->getOrCreateCourseChat($course, $studentId);
        $replyTo = $this->resolveReplyMessage($courseChat, $request->integer('reply_to_message_id'));
        if ($request->filled('reply_to_message_id') && !$replyTo) {
            return response()->json([
                'status' => false,
                'message' => 'Reply target not found in this chat thread',
            ], 422);
        }

        $message = $courseChat->messages()->create([
            'sender_type' => CourseChatMessage::SENDER_TEACHER,
            'sender_id' => $teacher->id,
            'reply_to_message_id' => $replyTo?->id,
            'body' => $request->body,
        ]);
        $message->load(['replyTo', 'mentions.student'])->loadCount('likes');

        event(new ChatMessageSent($message, 'direct', $course->id, $studentId));

        return response()->json([
            'status' => true,
            'message' => __('messages.message_sent'),
            'data' => $this->formatMessage($message),
        ], 201);
    }

    private function ensureStudentEnrolledInCourse(int $courseId, int $studentId): void
    {
        $exists = PaymentItem::where('course_id', $courseId)
            ->whereHas('payment', fn ($q) => $q->where('student_id', $studentId)->where('status', 'success'))
            ->exists();
        if (!$exists) {
            abort(404, 'Student not enrolled in this course.');
        }
    }
}
