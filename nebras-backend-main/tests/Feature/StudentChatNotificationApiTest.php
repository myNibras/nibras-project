<?php

namespace Tests\Feature;

use App\Events\ChatMessageSent;
use App\Models\ChatNotification;
use App\Models\Course;
use App\Models\CourseChat;
use App\Models\CourseChatMessage;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentChatNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeacher(): Teacher
    {
        return Teacher::create([
            'name' => 'Teacher ' . fake()->unique()->lastName(),
            'name_en' => 'Teacher ' . fake()->unique()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
            'years_of_experience' => 5,
        ]);
    }

    private function makeStudent(): Student
    {
        return Student::create([
            'name' => 'Student ' . fake()->unique()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
            'age' => 16,
            'gender' => 0,
            'phone' => fake()->unique()->numerify('05########'),
        ]);
    }

    private function makeCourse(Teacher $teacher): Course
    {
        $semesterId = DB::table('semesters')->insertGetId([
            'title' => 'Semester ' . fake()->unique()->word(),
            'title_en' => 'Semester ' . fake()->unique()->word(),
            'type' => 1,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $academicLevelId = DB::table('academic_levels')->insertGetId([
            'title' => 'Level ' . fake()->unique()->word(),
            'title_en' => 'Level ' . fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'slug_en' => fake()->unique()->slug(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Course::create([
            'semester_id' => $semesterId,
            'academic_level_id' => $academicLevelId,
            'teacher_id' => $teacher->id,
            'title' => 'Course ' . fake()->unique()->word(),
            'title_en' => 'Course ' . fake()->unique()->word(),
            'short_description' => 'short',
            'short_description_en' => 'short',
            'price' => 100.00,
            'duration' => '1m',
            'duration_en' => '1m',
            'schedule' => 'sched',
            'schedule_en' => 'sched',
            'slug' => fake()->unique()->slug(),
            'slug_en' => fake()->unique()->slug(),
            'course_type' => 'recorded',
            'payment_type' => 'one-off',
            'status' => true,
        ]);
    }

    private function enrollStudent(Student $student, Course $course): void
    {
        $payment = Payment::create([
            'student_id' => $student->id,
            'amount' => 100.00,
            'currency' => 'USD',
            'status' => 'success',
        ]);

        PaymentItem::create([
            'payment_id' => $payment->id,
            'course_id' => $course->id,
            'payment_type' => 'one-off',
            'quantity' => 1,
            'price' => 100.00,
            'total' => 100.00,
            'title' => $course->title,
            'title_en' => $course->title_en,
            'short_description' => 'short',
            'short_description_en' => 'short',
        ]);
    }

    /**
     * Set up an enrolled student in a course owned by a teacher.
     *
     * @return array{teacher: Teacher, course: Course, student: Student}
     */
    private function setupEnrolledStudent(): array
    {
        $teacher = $this->makeTeacher();
        $course = $this->makeCourse($teacher);
        $student = $this->makeStudent();
        $this->enrollStudent($student, $course);

        return [
            'teacher' => $teacher,
            'course' => $course,
            'student' => $student,
        ];
    }

    private function emitGroupMessage(Course $course, Teacher $teacher): CourseChatMessage
    {
        $courseChat = CourseChat::firstOrCreate(['course_id' => $course->id, 'student_id' => null]);
        $msg = CourseChatMessage::create([
            'course_chat_id' => $courseChat->id,
            'sender_type' => CourseChatMessage::SENDER_TEACHER,
            'sender_id' => $teacher->id,
            'body' => 'announcement',
        ]);
        event(new ChatMessageSent($msg, 'group', $course->id));
        return $msg;
    }

    public function test_unread_count_returns_zero_when_no_notifications(): void
    {
        $ctx = $this->setupEnrolledStudent();
        Sanctum::actingAs($ctx['student']);

        $response = $this->getJson('/api/v1/chat/notifications/unread-count');

        $response->assertOk();
        $this->assertSame(0, (int) $response->json('data.count'));
    }

    public function test_unread_count_reflects_unread_rows_for_student_only(): void
    {
        $ctx = $this->setupEnrolledStudent();
        $this->emitGroupMessage($ctx['course'], $ctx['teacher']);

        Sanctum::actingAs($ctx['student']);

        $response = $this->getJson('/api/v1/chat/notifications/unread-count');

        $response->assertOk();
        $this->assertSame(1, (int) $response->json('data.count'));
    }

    public function test_list_returns_unread_with_preview(): void
    {
        $ctx = $this->setupEnrolledStudent();
        $this->emitGroupMessage($ctx['course'], $ctx['teacher']);

        Sanctum::actingAs($ctx['student']);

        $response = $this->getJson('/api/v1/chat/notifications');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $entry = $response->json('data.0');
        $this->assertSame('group', $entry['thread_type']);
        $this->assertSame($ctx['course']->id, (int) $entry['course_id']);
        $this->assertNotEmpty($entry['body_preview']);
        $this->assertNotEmpty($entry['sender_name']);
    }

    public function test_mark_read_clears_only_matching_thread(): void
    {
        $ctx = $this->setupEnrolledStudent();
        $this->emitGroupMessage($ctx['course'], $ctx['teacher']);
        $this->emitGroupMessage($ctx['course'], $ctx['teacher']);

        Sanctum::actingAs($ctx['student']);

        $response = $this->postJson('/api/v1/chat/notifications/mark-read', [
            'course_id' => $ctx['course']->id,
            'thread_type' => 'group',
        ]);

        $response->assertOk();
        $this->assertSame(2, (int) $response->json('data.marked'));

        $countResponse = $this->getJson('/api/v1/chat/notifications/unread-count');
        $countResponse->assertOk();
        $this->assertSame(0, (int) $countResponse->json('data.count'));
    }

    public function test_endpoints_require_auth(): void
    {
        $this->getJson('/api/v1/chat/notifications/unread-count')->assertUnauthorized();
        $this->getJson('/api/v1/chat/notifications')->assertUnauthorized();
        $this->postJson('/api/v1/chat/notifications/mark-read', [
            'course_id' => 1,
            'thread_type' => 'group',
        ])->assertUnauthorized();
    }

    public function test_one_student_cannot_clear_anothers_notifications(): void
    {
        $ctxA = $this->setupEnrolledStudent();
        $ctxB = $this->setupEnrolledStudent();

        $this->emitGroupMessage($ctxA['course'], $ctxA['teacher']);
        $this->emitGroupMessage($ctxB['course'], $ctxB['teacher']);

        Sanctum::actingAs($ctxA['student']);

        $response = $this->postJson('/api/v1/chat/notifications/mark-read', [
            'course_id' => $ctxB['course']->id,
            'thread_type' => 'group',
        ]);

        $response->assertOk();
        $this->assertSame(0, (int) $response->json('data.marked'));

        $studentBUnread = ChatNotification::query()
            ->where('recipient_type', Student::class)
            ->where('recipient_id', $ctxB['student']->id)
            ->where('is_read', false)
            ->count();
        $this->assertSame(1, $studentBUnread);
    }
}
