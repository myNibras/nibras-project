<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\HomeSliderController;
use App\Http\Controllers\AcademicLevelController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\SettingController;

Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => 'auth'], function() {

    Route::resource('roles', RoleController::class);

    // Classes
    Route::get('classes', [ClassRoomController::class, 'index'])->middleware('permission:view classes')->name('classes.index');
    Route::get('classes/create', [ClassRoomController::class, 'create'])->middleware('permission:create classes')->name('classes.create');
    Route::post('classes', [ClassRoomController::class, 'store'])->middleware('permission:create classes')->name('classes.store');
    Route::get('classes/{class}', [ClassRoomController::class, 'show'])->middleware('permission:view classes')->name('classes.show');
    Route::get('classes/{class}/edit', [ClassRoomController::class, 'edit'])->middleware('permission:edit classes')->name('classes.edit');
    Route::put('classes/{class}', [ClassRoomController::class, 'update'])->middleware('permission:edit classes')->name('classes.update');
    Route::delete('classes/{class}', [ClassRoomController::class, 'destroy'])->middleware('permission:delete classes')->name('classes.destroy');

    // Admins
    Route::get('admins', [AdminController::class, 'index'])->middleware('permission:view admins')->name('admins.index');
    Route::get('admins/create', [AdminController::class, 'create'])->middleware('permission:create admins')->name('admins.create');
    Route::post('admins', [AdminController::class, 'store'])->middleware('permission:create admins')->name('admins.store');
    Route::get('admins/{admin}', [AdminController::class, 'show'])->middleware('permission:view admins')->name('admins.show');
    Route::get('admins/{admin}/edit', [AdminController::class, 'edit'])->middleware('permission:edit admins')->name('admins.edit');
    Route::put('admins/{admin}', [AdminController::class, 'update'])->middleware('permission:edit admins')->name('admins.update');
    Route::delete('admins/{admin}', [AdminController::class, 'destroy'])->middleware('permission:delete admins')->name('admins.destroy');

    // Courses
    Route::get('courses', [CourseController::class, 'index'])->middleware('permission:view courses')->name('courses.index');
    Route::get('courses/create', [CourseController::class, 'create'])->middleware('permission:create courses')->name('courses.create');
    Route::post('courses', [CourseController::class, 'store'])->middleware('permission:create courses')->name('courses.store');
    Route::get('courses/{course}', [CourseController::class, 'show'])->middleware('permission:view courses')->name('courses.show');
    Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->middleware('permission:edit courses')->name('courses.edit');
    Route::put('courses/{course}', [CourseController::class, 'update'])->middleware('permission:edit courses')->name('courses.update');
    Route::delete('courses/{course}', [CourseController::class, 'destroy'])->middleware('permission:delete courses')->name('courses.destroy');

    // Teachers
    Route::get('teachers', [TeacherController::class, 'index'])->middleware('permission:view teachers')->name('teachers.index');
    Route::get('teachers/create', [TeacherController::class, 'create'])->middleware('permission:create teachers')->name('teachers.create');
    Route::post('teachers', [TeacherController::class, 'store'])->middleware('permission:create teachers')->name('teachers.store');
    Route::get('teachers/{teacher}', [TeacherController::class, 'show'])->middleware('permission:view teachers')->name('teachers.show');
    Route::get('teachers/{teacher}/edit', [TeacherController::class, 'edit'])->middleware('permission:edit teachers')->name('teachers.edit');
    Route::put('teachers/{teacher}', [TeacherController::class, 'update'])->middleware('permission:edit teachers')->name('teachers.update');
    Route::delete('teachers/{teacher}', [TeacherController::class, 'destroy'])->middleware('permission:delete teachers')->name('teachers.destroy');
    Route::post('teachers/change-status/{id}', [TeacherController::class, 'changeStatus'])->middleware('permission:edit teachers')->name('teachers.change-status');
    Route::get('teachers/additional-info/{type}', [TeacherController::class, 'getAdditionalInfo'])->middleware('permission:edit teachers')->name('teachers.additional-info.get');
    Route::post('teachers/additional-info', [TeacherController::class, 'storeAdditionalInfo'])->middleware('permission:edit teachers')->name('teachers.additional-info.store');
    Route::get('teachers/{teacher}/login-as', [TeacherController::class, 'loginAs'])->middleware('permission:view teachers')->name('teachers.login-as');

    // Students
    Route::get('students', [StudentController::class, 'index'])->middleware('permission:view students')->name('students.index');
    Route::get('students/create', [StudentController::class, 'create'])->middleware('permission:create students')->name('students.create');
    Route::post('students', [StudentController::class, 'store'])->middleware('permission:create students')->name('students.store');
    Route::get('students/{student}', [StudentController::class, 'show'])->middleware('permission:view students')->name('students.show');
    Route::get('students/{student}/edit', [StudentController::class, 'edit'])->middleware('permission:edit students')->name('students.edit');
    Route::put('students/{student}', [StudentController::class, 'update'])->middleware('permission:edit students')->name('students.update');
    Route::delete('students/{student}', [StudentController::class, 'destroy'])->middleware('permission:delete students')->name('students.destroy');

    // Home Sliders
    Route::get('home-sliders', [HomeSliderController::class, 'index'])->middleware('permission:view home slider')->name('home-sliders.index');
    Route::get('home-sliders/create', [HomeSliderController::class, 'create'])->middleware('permission:create home slider')->name('home-sliders.create');
    Route::post('home-sliders', [HomeSliderController::class, 'store'])->middleware('permission:create home slider')->name('home-sliders.store');
    Route::get('home-sliders/{home_slider}', [HomeSliderController::class, 'show'])->middleware('permission:view home slider')->name('home-sliders.show');
    Route::get('home-sliders/{home_slider}/edit', [HomeSliderController::class, 'edit'])->middleware('permission:edit home slider')->name('home-sliders.edit');
    Route::put('home-sliders/{home_slider}', [HomeSliderController::class, 'update'])->middleware('permission:edit home slider')->name('home-sliders.update');
    Route::delete('home-sliders/{home_slider}', [HomeSliderController::class, 'destroy'])->middleware('permission:delete home slider')->name('home-sliders.destroy');

    // Semesters
    Route::get('semesters', [SemesterController::class, 'index'])->middleware('permission:view semesters')->name('semesters.index');
    Route::get('semesters/create', [SemesterController::class, 'create'])->middleware('permission:create semesters')->name('semesters.create');
    Route::post('semesters', [SemesterController::class, 'store'])->middleware('permission:create semesters')->name('semesters.store');
    Route::get('semesters/{semester}', [SemesterController::class, 'show'])->middleware('permission:view semesters')->name('semesters.show');
    Route::get('semesters/{semester}/edit', [SemesterController::class, 'edit'])->middleware('permission:edit semesters')->name('semesters.edit');
    Route::put('semesters/{semester}', [SemesterController::class, 'update'])->middleware('permission:edit semesters')->name('semesters.update');
    Route::delete('semesters/{semester}', [SemesterController::class, 'destroy'])->middleware('permission:delete semesters')->name('semesters.destroy');

    // Academic Levels
    Route::get('academic-levels', [AcademicLevelController::class, 'index'])->middleware('permission:view academic level')->name('academic-levels.index');
    Route::get('academic-levels/create', [AcademicLevelController::class, 'create'])->middleware('permission:create academic level')->name('academic-levels.create');
    Route::post('academic-levels', [AcademicLevelController::class, 'store'])->middleware('permission:create academic level')->name('academic-levels.store');
    Route::get('academic-levels/{academic_level}', [AcademicLevelController::class, 'show'])->middleware('permission:view academic level')->name('academic-levels.show');
    Route::get('academic-levels/{academic_level}/edit', [AcademicLevelController::class, 'edit'])->middleware('permission:edit academic level')->name('academic-levels.edit');
    Route::put('academic-levels/{academic_level}', [AcademicLevelController::class, 'update'])->middleware('permission:edit academic level')->name('academic-levels.update');
    Route::delete('academic-levels/{academic_level}', [AcademicLevelController::class, 'destroy'])->middleware('permission:delete academic level')->name('academic-levels.destroy');

    // Testimonials
    Route::get('testimonials', [TestimonialController::class, 'index'])->middleware('permission:view testimonials')->name('testimonials.index');
    Route::get('testimonials/admins', [TestimonialController::class, 'admins'])->middleware('permission:view testimonials')->name('testimonials.admins');
    Route::get('testimonials/students', [TestimonialController::class, 'students'])->middleware('permission:view testimonials')->name('testimonials.students');
    Route::get('testimonials/filter/courses', [TestimonialController::class, 'filterCourses'])->middleware('permission:view testimonials')->name('testimonials.filter.courses');
    Route::get('testimonials/create', [TestimonialController::class, 'create'])->middleware('permission:create testimonials')->name('testimonials.create');
    Route::post('testimonials', [TestimonialController::class, 'store'])->middleware('permission:create testimonials')->name('testimonials.store');
    Route::get('testimonials/{testimonial}', [TestimonialController::class, 'show'])->middleware('permission:view testimonials')->name('testimonials.show');
    Route::get('testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->middleware('permission:edit testimonials')->name('testimonials.edit');
    Route::put('testimonials/{testimonial}', [TestimonialController::class, 'update'])->middleware('permission:edit testimonials')->name('testimonials.update');
    Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->middleware('permission:delete testimonials')->name('testimonials.destroy');
    Route::post('testimonials/change-status/{id}', [TestimonialController::class, 'changeStatus'])->middleware('permission:edit testimonials')->name('testimonials.change-status');
    Route::get('testimonials/courses/by-class', [TestimonialController::class, 'getCoursesByClass'])->middleware('permission:create testimonials|edit testimonials')->name('testimonials.courses.by-class');
    Route::get('testimonials/additional-info/{type}', [TestimonialController::class, 'getAdditionalInfo'])->middleware('permission:edit testimonials')->name('testimonials.additional-info.get');
    Route::post('testimonials/additional-info', [TestimonialController::class, 'storeAdditionalInfo'])->middleware('permission:edit testimonials')->name('testimonials.additional-info.store');

    // News
    Route::get('news', [NewsController::class, 'index'])->middleware('permission:view news')->name('news.index');
    Route::get('news/create', [NewsController::class, 'create'])->middleware('permission:create news')->name('news.create');
    Route::post('news', [NewsController::class, 'store'])->middleware('permission:create news')->name('news.store');
    Route::get('news/{news}/edit', [NewsController::class, 'edit'])->middleware('permission:edit news')->name('news.edit');
    Route::put('news/{news}', [NewsController::class, 'update'])->middleware('permission:edit news')->name('news.update');
    Route::delete('news/{news}', [NewsController::class, 'destroy'])->middleware('permission:delete news')->name('news.destroy');
    Route::post('news/change-status/{id}', [NewsController::class, 'changeStatus'])->middleware('permission:edit news')->name('news.change-status');
    Route::post('news/upload-image', [NewsController::class, 'uploadImage'])->middleware('permission:create news|edit news')->name('news.upload-image');
    Route::get('news/additional-info/{type}', [NewsController::class, 'getAdditionalInfo'])->middleware('permission:edit news')->name('news.additional-info.get');
    Route::post('news/additional-info', [NewsController::class, 'storeAdditionalInfo'])->middleware('permission:edit news')->name('news.additional-info.store');

    // Articles
    Route::get('article', [ArticleController::class, 'index'])->middleware('permission:view article')->name('article.index');
    Route::get('article/create', [ArticleController::class, 'create'])->middleware('permission:create article')->name('article.create');
    Route::post('article', [ArticleController::class, 'store'])->middleware('permission:create article')->name('article.store');
    Route::get('article/{article}/edit', [ArticleController::class, 'edit'])->middleware('permission:edit article')->name('article.edit');
    Route::put('article/{article}', [ArticleController::class, 'update'])->middleware('permission:edit article')->name('article.update');
    Route::delete('article/{article}', [ArticleController::class, 'destroy'])->middleware('permission:delete article')->name('article.destroy');
    Route::post('article/change-status/{id}', [ArticleController::class, 'changeStatus'])->middleware('permission:edit article')->name('article.change-status');
    Route::post('article/upload-image', [ArticleController::class, 'uploadImage'])->middleware('permission:create article|edit article')->name('article.upload-image');
    Route::get('article/additional-info/{type}', [ArticleController::class, 'getAdditionalInfo'])->middleware('permission:edit article')->name('article.additional-info.get');
    Route::post('article/additional-info', [ArticleController::class, 'storeAdditionalInfo'])->middleware('permission:edit article')->name('article.additional-info.store');
    // Partners
    Route::get('partners', [PartnerController::class, 'index'])->middleware('permission:view partners')->name('partners.index');
    Route::get('partners/create', [PartnerController::class, 'create'])->middleware('permission:create partners')->name('partners.create');
    Route::post('partners', [PartnerController::class, 'store'])->middleware('permission:create partners')->name('partners.store');
    Route::get('partners/{partner}/edit', [PartnerController::class, 'edit'])->middleware('permission:edit partners')->name('partners.edit');
    Route::put('partners/{partner}', [PartnerController::class, 'update'])->middleware('permission:edit partners')->name('partners.update');
    Route::delete('partners/{partner}', [PartnerController::class, 'destroy'])->middleware('permission:delete partners')->name('partners.destroy');
    Route::post('partners/change-status/{id}', [PartnerController::class, 'changeStatus'])->middleware('permission:edit partners')->name('partners.change-status');
    Route::get('partners/additional-info/{type}', [PartnerController::class, 'getAdditionalInfo'])->middleware('permission:edit partners')->name('partners.additional-info.get');
    Route::post('partners/additional-info', [PartnerController::class, 'storeAdditionalInfo'])->middleware('permission:edit partners')->name('partners.additional-info.store');
    // Coupons
    Route::get('coupons', [CouponController::class, 'index'])->middleware('permission:view coupons')->name('coupons.index');
    Route::get('coupons/create', [CouponController::class, 'create'])->middleware('permission:create coupons')->name('coupons.create');
    Route::post('coupons', [CouponController::class, 'store'])->middleware('permission:create coupons')->name('coupons.store');
    Route::get('coupons/generate-code', [CouponController::class, 'generateCode'])->middleware('permission:create coupons|edit coupons')->name('coupons.generate-code');
    Route::get('coupons/{coupon}', [CouponController::class, 'show'])->middleware('permission:view coupons')->name('coupons.show');
    Route::get('coupons/{coupon}/edit', [CouponController::class, 'edit'])->middleware('permission:edit coupons')->name('coupons.edit');
    Route::put('coupons/{coupon}', [CouponController::class, 'update'])->middleware('permission:edit coupons')->name('coupons.update');
    Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->middleware('permission:delete coupons')->name('coupons.destroy');
    Route::post('coupons/change-status/{id}', [CouponController::class, 'change_status'])->middleware('permission:edit coupons')->name('coupons.change-status');

    // Candidates (Candidate Submitted)
    Route::get('candidates', [CandidateController::class, 'index'])->middleware('permission:view candidates')->name('candidates.index');
    Route::get('candidates/{candidate}', [CandidateController::class, 'show'])->middleware('permission:view candidates')->name('candidates.show');
    Route::delete('candidates/{candidate}', [CandidateController::class, 'destroy'])->middleware('permission:delete candidates')->name('candidates.destroy');

    // FAQs
    Route::get('faqs', [FaqController::class, 'index'])->middleware('permission:view faqs')->name('faqs.index');
    Route::post('faqs/reorder', [FaqController::class, 'reorder'])->middleware('permission:edit faqs')->name('faqs.reorder');
    Route::get('faqs/create', [FaqController::class, 'create'])->middleware('permission:create faqs')->name('faqs.create');
    Route::post('faqs', [FaqController::class, 'store'])->middleware('permission:create faqs')->name('faqs.store');
    Route::get('faqs/{faq}', [FaqController::class, 'show'])->middleware('permission:view faqs')->name('faqs.show');
    Route::get('faqs/{faq}/edit', [FaqController::class, 'edit'])->middleware('permission:edit faqs')->name('faqs.edit');
    Route::put('faqs/{faq}', [FaqController::class, 'update'])->middleware('permission:edit faqs')->name('faqs.update');
    Route::delete('faqs/{faq}', [FaqController::class, 'destroy'])->middleware('permission:delete faqs')->name('faqs.destroy');
    Route::post('faqs/change-status/{id}', [FaqController::class, 'change_status'])->middleware('permission:edit faqs')->name('faqs.change-status');
    Route::get('faqs/additional-info/{type}', [FaqController::class, 'getAdditionalInfo'])->middleware('permission:edit faqs')->name('faqs.additional-info.get');
    Route::post('faqs/additional-info', [FaqController::class, 'storeAdditionalInfo'])->middleware('permission:edit faqs')->name('faqs.additional-info.store');
    // Settings (view + form save)
    Route::get('settings', [SettingController::class, 'index'])->middleware('permission:view settings')->name('settings.index');
    Route::post('settings', [SettingController::class, 'updateAll'])->middleware('permission:edit settings')->name('settings.update-all');
    // Positions
    Route::get('positions', [PositionController::class, 'index'])->middleware('permission:view positions')->name('positions.index');
    Route::get('positions/create', [PositionController::class, 'create'])->middleware('permission:create positions')->name('positions.create');
    Route::post('positions', [PositionController::class, 'store'])->middleware('permission:create positions')->name('positions.store');
    Route::get('positions/{position}/edit', [PositionController::class, 'edit'])->middleware('permission:edit positions')->name('positions.edit');
    Route::put('positions/{position}', [PositionController::class, 'update'])->middleware('permission:edit positions')->name('positions.update');
    Route::delete('positions/{position}', [PositionController::class, 'destroy'])->middleware('permission:delete positions')->name('positions.destroy');
    Route::post('positions/change-status/{id}', [PositionController::class, 'changeStatus'])->middleware('permission:edit positions')->name('positions.change-status');

    // Payments (custom routes)
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index')->middleware('permission:view payments');
    Route::get('payments/{id}', [PaymentController::class, 'show'])->name('payments.show')->middleware('permission:view payments');

    // Installments (payments with installments - paid/total and details)
    Route::get('installments', [InstallmentController::class, 'index'])->name('installments.index')->middleware('permission:view installments');

    // Semester status change
    Route::post('semesters/change-status/{id}', [SemesterController::class, 'change_status'])->middleware('permission:edit semesters');
    // Admin status change
    Route::post('admins/change-status/{id}', [AdminController::class, 'change_status'])->middleware('permission:edit admins');
    // Course status change
    Route::post('courses/change-status/{id}', [CourseController::class, 'change_status'])->middleware('permission:edit courses');
    // Copy course
    Route::post('courses/copy/{id}', [CourseController::class, 'copy'])->middleware('permission:copy course');

    // Contact submissions
    Route::get('contact-submissions', [ContactSubmissionController::class, 'index'])->middleware('permission:view contact submissions')->name('contact-submissions.index');
    Route::get('contact-submissions/{contact_submission}', [ContactSubmissionController::class, 'show'])->middleware('permission:view contact submissions')->name('contact-submissions.show');
    Route::delete('contact-submissions/{contact_submission}', [ContactSubmissionController::class, 'destroy'])->middleware('permission:delete contact submissions')->name('contact-submissions.destroy');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');
    Route::get('/semester-dashboard', [DashboardController::class, 'semesterDashboard'])->name('dashboard.semester')->middleware('auth');
    Route::get('/semester-dashboard/stats', [DashboardController::class, 'semesterDashboardStats'])->name('dashboard.semester.stats')->middleware('auth');
    Route::get('/semester-dashboard/teacher-stats', [DashboardController::class, 'semesterDashboardTeacherStats'])->name('dashboard.semester.teacher-stats')->middleware('auth');
    Route::get('/semester-dashboard/export-student-stats', [DashboardController::class, 'exportSemesterStudentStats'])->name('dashboard.semester.export.student-stats')->middleware('auth');
    Route::get('/semester-dashboard/export-teacher-stats', [DashboardController::class, 'exportSemesterTeacherStats'])->name('dashboard.semester.export.teacher-stats')->middleware('auth');
    Route::get('/semester-dashboard/academic-level-stats', [DashboardController::class, 'semesterDashboardAcademicLevelStats'])->name('dashboard.semester.academic-level-stats')->middleware('auth');
    Route::get('/semester-dashboard/export-academic-level-stats', [DashboardController::class, 'exportSemesterAcademicLevelStats'])->name('dashboard.semester.export.academic-level-stats')->middleware('auth');
    Route::get('/semester-dashboard/subject-stats', [DashboardController::class, 'semesterDashboardSubjectStats'])->name('dashboard.semester.subject-stats')->middleware('auth');
    Route::get('/semester-dashboard/export-subject-stats', [DashboardController::class, 'exportSemesterSubjectStats'])->name('dashboard.semester.export.subject-stats')->middleware('auth');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats')->middleware('auth');
    Route::post('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export')->middleware('auth');;

});

Route::get('refresh-csrf', function(){
    return csrf_token();
});

require __DIR__.'/auth.php';
