<?php

use App\Http\Controllers\Teacher\LoginController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\ProfileController;
use App\Http\Controllers\Teacher\CoursesController;
use App\Http\Controllers\Teacher\ChatController;
use App\Http\Controllers\Teacher\ChatNotificationController;
use App\Http\Controllers\Teacher\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
|
| Routes for teacher login and dashboard. Prefix: /teacher, Name: teacher.*
|
*/

Route::prefix('teacher')->name('teacher.')->group(function () {
    Route::middleware('guest:teacher')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
    });

    Route::middleware(['auth:teacher', 'teacher.impersonate.readonly'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('semester-dashboard/stats', [DashboardController::class, 'semesterDashboardStats'])->name('dashboard.semester.stats');
        Route::get('semester-dashboard/teacher-stats', [DashboardController::class, 'semesterDashboardTeacherStats'])->name('dashboard.semester.teacher-stats');
        Route::get('semester-dashboard/academic-level-stats', [DashboardController::class, 'semesterDashboardAcademicLevelStats'])->name('dashboard.semester.academic-level-stats');
        Route::get('semester-dashboard/subject-stats', [DashboardController::class, 'semesterDashboardSubjectStats'])->name('dashboard.semester.subject-stats');
        Route::get('profile', [ProfileController::class, 'index'])->name('profile');
        Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('courses', [CoursesController::class, 'index'])->name('courses');
        Route::get('courses/{id}', [CoursesController::class, 'show'])->name('courses.show')->whereNumber('id');
        Route::get('courses/{id}/edit', [CoursesController::class, 'edit'])->name('courses.edit')->whereNumber('id');
        Route::put('courses/{id}', [CoursesController::class, 'update'])->name('courses.update')->whereNumber('id');
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::get('chat', [ChatController::class, 'index'])->name('chat');
        Route::get('courses/{course}/chat/students', [ChatController::class, 'getEnrolledStudents'])->name('chat.students');
        Route::get('courses/{course}/chat/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('courses/{course}/chat/messages', [ChatController::class, 'sendMessage'])->name('chat.send');
        Route::get('courses/{course}/chat/direct/{student}/messages', [ChatController::class, 'getDirectMessages'])->name('chat.direct.messages');
        Route::post('courses/{course}/chat/direct/{student}/messages', [ChatController::class, 'sendDirectMessage'])->name('chat.direct.send');
        Route::get('chat/notifications/unread-count', [ChatNotificationController::class, 'unreadCount'])->name('chat.notifications.count');
        Route::get('chat/notifications', [ChatNotificationController::class, 'index'])->name('chat.notifications.index');
        Route::post('chat/notifications/mark-read', [ChatNotificationController::class, 'markRead'])->name('chat.notifications.mark-read');
        Route::get('leave-impersonation', [LoginController::class, 'leaveImpersonation'])->name('leave-impersonation');
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    });
});
