<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseChat;
use App\Models\CourseChatMessage;
use App\Models\PaymentItem;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseChatController extends Controller
{
    private function formatMessage(CourseChatMessage $msg, int $studentId): array
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
            'liked_by_me' => $msg->likes()->where('student_id', $studentId)->exists(),
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

    private function getEnrolledStudentIds(int $courseId): array
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

    /**
     * Ensure the authenticated student is enrolled in the course (has successful payment).
     */
    private function ensureStudentEnrolled(int $studentId, int $courseId): bool
    {
        return PaymentItem::where('course_id', $courseId)
            ->whereHas('payment', function ($q) use ($studentId) {
                $q->where('student_id', $studentId)->where('status', 'success');
            })
            ->exists();
    }

    /**
     * Get or create a course chat.
     * @param int|null $studentId null = course-wide (one-to-many), set = direct thread (one-to-one)
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
     * List messages for a course chat. Student must be enrolled.
     */
    public function index(Request $request, $course): JsonResponse
    {
        $courseId = (int) $course;
        /** @var \App\Models\Student $student */
        $student = $request->user();

        if (!$this->ensureStudentEnrolled($student->id, $courseId)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.unauthorized_course_chat'),
            ], 403);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => false, 'message' => 'Course not found'], 404);
        }

        $courseChat = $this->getOrCreateCourseChat($course, null);
        $messages = $courseChat->messages()
            ->with(['replyTo', 'mentions.student'])
            ->withCount('likes')
            ->orderBy('created_at')
            ->get()
            ->map(fn (CourseChatMessage $msg) => $this->formatMessage($msg, $student->id));

        return response()->json([
            'status' => true,
            'message' => 'Messages fetched successfully',
            'data' => $messages,
        ]);
    }

    /**
     * Send a message. Student must be enrolled.
     */
    public function store(Request $request, $course): JsonResponse
    {
        $request->validate([
            'body' => 'required|string|max:4000',
            'reply_to_message_id' => 'nullable|integer|exists:course_chat_messages,id',
            'mentioned_student_ids' => 'nullable|array',
            'mentioned_student_ids.*' => 'integer|exists:students,id',
        ]);

        $courseId = (int) $course;
        /** @var \App\Models\Student $student */
        $student = $request->user();

        if (!$this->ensureStudentEnrolled($student->id, $courseId)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.unauthorized_course_chat'),
            ], 403);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => false, 'message' => 'Course not found'], 404);
        }

        $courseChat = $this->getOrCreateCourseChat($course, null);
        $enrolledIds = $this->getEnrolledStudentIds($courseId);
        $mentionedIds = collect($request->input('mentioned_student_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $invalidMention = $mentionedIds->first(fn ($id) => !in_array($id, $enrolledIds, true));
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
            'sender_type' => CourseChatMessage::SENDER_STUDENT,
            'sender_id' => $student->id,
            'reply_to_message_id' => $replyTo?->id,
            'body' => $request->body,
        ]);
        if ($mentionedIds->isNotEmpty()) {
            $message->mentions()->createMany(
                $mentionedIds->map(fn ($id) => ['student_id' => $id])->all()
            );
        }
        $message->load(['replyTo', 'mentions.student'])->loadCount('likes');
        event(new ChatMessageSent($message, 'group', $courseId));

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $this->formatMessage($message, $student->id),
        ], 201);
    }

    /**
     * List messages for the authenticated student's direct (one-to-one) chat with the teacher.
     */
    public function indexDirect(Request $request, $course): JsonResponse
    {
        $courseId = (int) $course;
        /** @var \App\Models\Student $student */
        $student = $request->user();

        if (!$this->ensureStudentEnrolled($student->id, $courseId)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.unauthorized_course_chat'),
            ], 403);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => false, 'message' => 'Course not found'], 404);
        }

        $courseChat = $this->getOrCreateCourseChat($course, $student->id);
        $messages = $courseChat->messages()
            ->with(['replyTo', 'mentions.student'])
            ->withCount('likes')
            ->orderBy('created_at')
            ->get()
            ->map(fn (CourseChatMessage $msg) => $this->formatMessage($msg, $student->id));

        return response()->json([
            'status' => true,
            'message' => 'Messages fetched successfully',
            'data' => $messages,
        ]);
    }

    /**
     * Send a message in the student's direct (one-to-one) chat with the teacher.
     */
    public function storeDirect(Request $request, $course): JsonResponse
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

        $courseId = (int) $course;
        /** @var \App\Models\Student $student */
        $student = $request->user();

        if (!$this->ensureStudentEnrolled($student->id, $courseId)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.unauthorized_course_chat'),
            ], 403);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => false, 'message' => 'Course not found'], 404);
        }

        $courseChat = $this->getOrCreateCourseChat($course, $student->id);
        $replyTo = $this->resolveReplyMessage($courseChat, $request->integer('reply_to_message_id'));
        if ($request->filled('reply_to_message_id') && !$replyTo) {
            return response()->json([
                'status' => false,
                'message' => 'Reply target not found in this chat thread',
            ], 422);
        }
        $message = $courseChat->messages()->create([
            'sender_type' => CourseChatMessage::SENDER_STUDENT,
            'sender_id' => $student->id,
            'reply_to_message_id' => $replyTo?->id,
            'body' => $request->body,
        ]);
        $message->load(['replyTo', 'mentions.student'])->loadCount('likes');
        event(new ChatMessageSent($message, 'direct', $courseId, $student->id));

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $this->formatMessage($message, $student->id),
        ], 201);
    }

    public function groupParticipants(Request $request, $course): JsonResponse
    {
        $courseId = (int) $course;
        $student = $request->user();

        if (!$this->ensureStudentEnrolled($student->id, $courseId)) {
            return response()->json(['status' => false, 'message' => __('messages.unauthorized_course_chat')], 403);
        }

        $participants = Student::whereIn('id', $this->getEnrolledStudentIds($courseId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]);

        return response()->json(['status' => true, 'data' => $participants]);
    }

    public function likeGroupMessage(Request $request, $course, int $message): JsonResponse
    {
        $courseId = (int) $course;
        $student = $request->user();
        if (!$this->ensureStudentEnrolled($student->id, $courseId)) {
            return response()->json(['status' => false, 'message' => __('messages.unauthorized_course_chat')], 403);
        }
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => false, 'message' => 'Course not found'], 404);
        }
        $courseChat = $this->getOrCreateCourseChat($course, null);
        $target = $courseChat->messages()->whereKey($message)->first();
        if (!$target) {
            return response()->json(['status' => false, 'message' => 'Message not found in this group chat'], 404);
        }
        $target->likes()->firstOrCreate(['student_id' => $student->id]);
        return response()->json(['status' => true]);
    }

    public function unlikeGroupMessage(Request $request, $course, int $message): JsonResponse
    {
        $courseId = (int) $course;
        $student = $request->user();
        if (!$this->ensureStudentEnrolled($student->id, $courseId)) {
            return response()->json(['status' => false, 'message' => __('messages.unauthorized_course_chat')], 403);
        }
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => false, 'message' => 'Course not found'], 404);
        }
        $courseChat = $this->getOrCreateCourseChat($course, null);
        $target = $courseChat->messages()->whereKey($message)->first();
        if (!$target) {
            return response()->json(['status' => false, 'message' => 'Message not found in this group chat'], 404);
        }
        $target->likes()->where('student_id', $student->id)->delete();
        return response()->json(['status' => true]);
    }
}
