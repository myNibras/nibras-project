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

class ChatNotificationCreationTest extends TestCase
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
        // Seed required parent rows for FKs.
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

    public function test_group_message_from_teacher_notifies_all_students_except_sender(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->makeCourse($teacher);
        $studentA = $this->makeStudent();
        $studentB = $this->makeStudent();
        $this->enrollStudent($studentA, $course);
        $this->enrollStudent($studentB, $course);

        $chat = CourseChat::create([
            'course_id' => $course->id,
            'student_id' => null,
        ]);

        $message = CourseChatMessage::create([
            'course_chat_id' => $chat->id,
            'sender_type' => CourseChatMessage::SENDER_TEACHER,
            'sender_id' => $teacher->id,
            'body' => 'Hello class',
        ]);

        event(new ChatMessageSent($message, 'group', $course->id, null));

        $notifications = ChatNotification::where('chat_message_id', $message->id)->get();
        $this->assertCount(2, $notifications);

        foreach ($notifications as $n) {
            $this->assertSame(Student::class, $n->recipient_type);
            $this->assertFalse((bool) $n->is_read);
            $this->assertSame('group', $n->thread_type);
        }

        $recipientIds = $notifications->pluck('recipient_id')->map(fn ($v) => (int) $v)->sort()->values()->all();
        $expected = collect([$studentA->id, $studentB->id])->sort()->values()->all();
        $this->assertSame($expected, $recipientIds);

        // Sender (teacher) should NOT have a notification.
        $this->assertSame(0, ChatNotification::where('chat_message_id', $message->id)
            ->where('recipient_type', Teacher::class)
            ->count());
    }

    public function test_group_message_from_student_notifies_other_students_and_teacher(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->makeCourse($teacher);
        $sender = $this->makeStudent();
        $other = $this->makeStudent();
        $this->enrollStudent($sender, $course);
        $this->enrollStudent($other, $course);

        $chat = CourseChat::create([
            'course_id' => $course->id,
            'student_id' => null,
        ]);

        $message = CourseChatMessage::create([
            'course_chat_id' => $chat->id,
            'sender_type' => CourseChatMessage::SENDER_STUDENT,
            'sender_id' => $sender->id,
            'body' => 'Hi everyone',
        ]);

        event(new ChatMessageSent($message, 'group', $course->id, null));

        $notifications = ChatNotification::where('chat_message_id', $message->id)->get();
        $this->assertCount(2, $notifications);

        // One row for the teacher.
        $teacherRow = $notifications->firstWhere('recipient_type', Teacher::class);
        $this->assertNotNull($teacherRow);
        $this->assertSame($teacher->id, (int) $teacherRow->recipient_id);
        $this->assertSame('group', $teacherRow->thread_type);
        $this->assertFalse((bool) $teacherRow->is_read);

        // One row for the OTHER student.
        $studentRow = $notifications->firstWhere('recipient_type', Student::class);
        $this->assertNotNull($studentRow);
        $this->assertSame($other->id, (int) $studentRow->recipient_id);
        $this->assertSame('group', $studentRow->thread_type);
        $this->assertFalse((bool) $studentRow->is_read);

        // Sender must not receive a notification.
        $this->assertSame(0, ChatNotification::where('chat_message_id', $message->id)
            ->where('recipient_type', Student::class)
            ->where('recipient_id', $sender->id)
            ->count());
    }

    public function test_direct_message_from_student_notifies_only_teacher(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->makeCourse($teacher);
        $student = $this->makeStudent();
        $this->enrollStudent($student, $course);

        $chat = CourseChat::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
        ]);

        $message = CourseChatMessage::create([
            'course_chat_id' => $chat->id,
            'sender_type' => CourseChatMessage::SENDER_STUDENT,
            'sender_id' => $student->id,
            'body' => 'Question for teacher',
        ]);

        event(new ChatMessageSent($message, 'direct', $course->id, $student->id));

        $notifications = ChatNotification::where('chat_message_id', $message->id)->get();
        $this->assertCount(1, $notifications);

        $n = $notifications->first();
        $this->assertSame(Teacher::class, $n->recipient_type);
        $this->assertSame($teacher->id, (int) $n->recipient_id);
        $this->assertSame('direct', $n->thread_type);
        $this->assertSame(Student::class, $n->thread_partner_type);
        $this->assertSame($student->id, (int) $n->thread_partner_id);
        $this->assertFalse((bool) $n->is_read);
    }

    public function test_direct_message_from_teacher_notifies_only_student(): void
    {
        $teacher = $this->makeTeacher();
        $course = $this->makeCourse($teacher);
        $student = $this->makeStudent();
        $this->enrollStudent($student, $course);

        $chat = CourseChat::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
        ]);

        $message = CourseChatMessage::create([
            'course_chat_id' => $chat->id,
            'sender_type' => CourseChatMessage::SENDER_TEACHER,
            'sender_id' => $teacher->id,
            'body' => 'Reply to student',
        ]);

        event(new ChatMessageSent($message, 'direct', $course->id, $student->id));

        $notifications = ChatNotification::where('chat_message_id', $message->id)->get();
        $this->assertCount(1, $notifications);

        $n = $notifications->first();
        $this->assertSame(Student::class, $n->recipient_type);
        $this->assertSame($student->id, (int) $n->recipient_id);
        $this->assertSame('direct', $n->thread_type);
        $this->assertSame(Teacher::class, $n->thread_partner_type);
        $this->assertSame($teacher->id, (int) $n->thread_partner_id);
        $this->assertFalse((bool) $n->is_read);
    }
}
