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
use Tests\TestCase;

class TeacherChatNotificationApiTest extends TestCase
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

    private function emitDirectFromStudent(Course $course, Student $student): void
    {
        $cc = CourseChat::firstOrCreate(['course_id' => $course->id, 'student_id' => $student->id]);
        $msg = CourseChatMessage::create([
            'course_chat_id' => $cc->id,
            'sender_type' => CourseChatMessage::SENDER_STUDENT,
            'sender_id' => $student->id,
            'body' => 'help me',
        ]);
        event(new ChatMessageSent($msg, 'direct', $course->id, $student->id));
    }

    public function test_unread_count_for_teacher(): void
    {
        $ctx = $this->setupEnrolledStudent();
        $this->emitDirectFromStudent($ctx['course'], $ctx['student']);

        $this->actingAs($ctx['teacher'], 'teacher');

        $response = $this->getJson('/teacher/chat/notifications/unread-count');

        $response->assertOk();
        $this->assertSame(1, (int) $response->json('data.count'));
    }

    public function test_index_lists_unread_for_teacher(): void
    {
        $ctx = $this->setupEnrolledStudent();
        $this->emitDirectFromStudent($ctx['course'], $ctx['student']);

        $this->actingAs($ctx['teacher'], 'teacher');

        $response = $this->getJson('/teacher/chat/notifications');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $entry = $response->json('data.0');
        $this->assertSame('direct', $entry['thread_type']);
        $this->assertSame($ctx['student']->id, (int) $entry['thread_partner_id']);
    }

    public function test_mark_read_for_direct_thread(): void
    {
        $ctx = $this->setupEnrolledStudent();
        $this->emitDirectFromStudent($ctx['course'], $ctx['student']);

        $this->actingAs($ctx['teacher'], 'teacher');

        $response = $this->postJson('/teacher/chat/notifications/mark-read', [
            'course_id' => $ctx['course']->id,
            'thread_type' => 'direct',
            'thread_partner_id' => $ctx['student']->id,
        ]);

        $response->assertOk();
        $this->assertSame(1, (int) $response->json('data.marked'));

        $remaining = ChatNotification::query()
            ->where('recipient_type', Teacher::class)
            ->where('recipient_id', $ctx['teacher']->id)
            ->where('is_read', false)
            ->count();
        $this->assertSame(0, $remaining);
    }

    public function test_endpoints_require_teacher_auth(): void
    {
        $this->get('/teacher/chat/notifications/unread-count')->assertStatus(302);
        $this->get('/teacher/chat/notifications')->assertStatus(302);
        $this->post('/teacher/chat/notifications/mark-read', [
            'course_id' => 1,
            'thread_type' => 'direct',
            'thread_partner_id' => 1,
        ])->assertStatus(302);
    }
}
