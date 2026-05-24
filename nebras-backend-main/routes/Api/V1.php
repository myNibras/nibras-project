<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\HomeSliderController;
use App\Http\Controllers\Api\V1\AcademicLevelController;
use App\Http\Controllers\Api\V1\StudentProfileController;
use App\Http\Controllers\Api\V1\CommonController;
use App\Http\Controllers\Api\V1\PasswordController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\PartnerController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Controllers\Api\V1\ContactSubmissionController;
use App\Http\Controllers\Api\V1\CandidateController;
use App\Http\Controllers\Api\V1\CourseChatController;
use App\Http\Controllers\Api\V1\ChatNotificationController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('auth/google', [AuthController::class, 'authGoogle']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/home-sliders', [HomeSliderController::class, 'index']);
Route::get('/home-sliders/{id}', [HomeSliderController::class, 'show']);

Route::get('/academic-levels', [AcademicLevelController::class, 'index']);
Route::get('/academic-levels/{id}', [AcademicLevelController::class, 'show']);
Route::get('/academic-levels/slug/{slug}', [AcademicLevelController::class, 'showBySlug']);

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/related-courses', [CourseController::class, 'relatedCourses']);
Route::get('/courses/{id}', [CourseController::class, 'show']);
// Chat routes must come BEFORE showBySlug - otherwise /courses/123/chat/messages matches showBySlug (Academic Level not found)
Route::get('courses/{course}/chat/messages', [CourseChatController::class, 'index'])->middleware('auth:sanctum');
Route::post('courses/{course}/chat/messages', [CourseChatController::class, 'store'])->middleware('auth:sanctum');
Route::get('courses/{course}/chat/group/participants', [CourseChatController::class, 'groupParticipants'])->middleware('auth:sanctum');
Route::post('courses/{course}/chat/group/messages/{message}/like', [CourseChatController::class, 'likeGroupMessage'])->middleware('auth:sanctum');
Route::delete('courses/{course}/chat/group/messages/{message}/like', [CourseChatController::class, 'unlikeGroupMessage'])->middleware('auth:sanctum');
Route::get('courses/{course}/chat/direct', [CourseChatController::class, 'indexDirect'])->middleware('auth:sanctum');
Route::post('courses/{course}/chat/direct', [CourseChatController::class, 'storeDirect'])->middleware('auth:sanctum');
Route::get('chat/notifications/unread-count', [ChatNotificationController::class, 'unreadCount'])->middleware('auth:sanctum');
Route::get('chat/notifications', [ChatNotificationController::class, 'index'])->middleware('auth:sanctum');
Route::post('chat/notifications/mark-read', [ChatNotificationController::class, 'markRead'])->middleware('auth:sanctum');
Route::get('/courses/{academic_level_slug}/{course_slug}/{course_id}', [CourseController::class, 'showBySlug']);

Route::get('/classes', [CommonController::class, 'getAllClasses']);

Route::get('/testimonials', [TestimonialController::class, 'index']);
Route::get('/testimonials/{id}', [TestimonialController::class, 'show']);

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/related-news', [NewsController::class, 'relatedNews']);
Route::get('/news/{id}', [NewsController::class, 'show']);

Route::get('/partners', [PartnerController::class, 'index']);
Route::get('/partners/{id}', [PartnerController::class, 'show']);

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/related-articles', [ArticleController::class, 'relatedArticles']);
Route::get('/articles/{id}', [ArticleController::class, 'show']);

Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/faqs/{id}', [FaqController::class, 'show']);

Route::get('/teachers', [TeacherController::class, 'index']);
Route::get('/teachers/{id}', [TeacherController::class, 'show']);

Route::post('/contact', [ContactSubmissionController::class, 'store']);

Route::post('/candidates', [CandidateController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [StudentProfileController::class, 'show']);
    Route::put('/profile', [StudentProfileController::class, 'update']);
    Route::post('/profile/change-password', [StudentProfileController::class, 'changePassword']);
    
    Route::get('/profile/materials', [StudentProfileController::class, 'getMaterials']);
    Route::get('/profile/testimonials', [StudentProfileController::class, 'getTestimonials']);
    Route::post('/profile/testimonials', [StudentProfileController::class, 'storeTestimonial']);
    Route::get('/profile/payments', [StudentProfileController::class, 'getPayments']);
    Route::get('/profile/notifications', [StudentProfileController::class, 'getNotifications']);
    Route::get('/profile/unpaid-invoices-next-two-days', [StudentProfileController::class, 'hasUnpaidInvoicesInNextTwoDays']);

    Route::post('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments/installments/create-session', [PaymentController::class, 'createInstallmentSession'])->name('payments.installments.create-session');
    Route::get('cart', [PaymentController::class, 'getCart'])->name('cart.get');
    Route::post('cart/add', [PaymentController::class, 'addToCart'])->name('cart.add');
    Route::delete('cart/remove', [PaymentController::class, 'removeFromCart'])->name('cart.remove');
    
    Route::post('coupons/add', [CouponController::class, 'addCoupon'])->name('coupons.add');
    Route::delete('coupons/remove', [CouponController::class, 'removeCoupon'])->name('coupons.remove');
});

Route::get('payments/callback', [PaymentController::class, 'callback'])->name('payments.callback_get');
Route::post('payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');

Route::prefix('password')->group(function() {
    Route::post('send-otp', [PasswordController::class, 'sendOtp']);
    Route::post('verify-otp', [PasswordController::class, 'verifyOtp']);
    Route::post('reset', [PasswordController::class, 'resetPassword']);
});
