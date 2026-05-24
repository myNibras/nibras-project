<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Student;
use App\Models\Course;
use App\Models\Installment;
use App\Models\PaymentItem;
use App\Models\Testimonial;
use App\Models\AdditionalInformation;
use App\Models\Notification;
use App\Models\Setting;
use Carbon\Carbon;
use App\Http\Resources\Api\V1\StudentResource;
use App\Http\Resources\Api\V1\RecordedMaterialResource;
use App\Http\Resources\Api\V1\TestimonialResource;
use App\Http\Resources\Api\V1\StudentPaymentByItemResource;
use App\Http\Resources\Api\V1\NotificationResource;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StudentProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'status'  => true,
            'message' => 'Profile fetched successfully',
            'data'    => new StudentResource($request->user()),
        ]);
    }

    public function update(Request $request)
    {
        try {
            /** @var Student $student */
            $student = $request->user();
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => ['required', 'email', Rule::unique('students')->ignore($student->id)],
                'phone'    => ['required', 'string'],
                'country'  => ['required', 'string'],
                'age'      => 'sometimes|integer|min:3|max:100',
                'gender'   => 'required|in:0,1',
                'class_id' => 'required|exists:classes,id',
                'password' => 'nullable|min:6',
                'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            ]);

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Remove profile_picture from validated array as it's handled separately
            unset($validated['profile_picture']);

            $student->update($validated);

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Clear existing profile picture
                $student->clearMediaCollection('profile_pictures');
                // Add new profile picture
                $student->addMedia($request->file('profile_picture'))->toMediaCollection('profile_pictures');
            }

            return response()->json([
                'status'  => true,
                'message' => 'Profile updated successfully',
                'data'    => new StudentResource($student->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update profile',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getMaterials(Request $request)
    {
        try {
            /** @var Student $student */
            $student = $request->user();

            // Get all payment items for the student with successful payments
            $paymentItems = PaymentItem::with(['course.teacher', 'course.semester', 'course.academicLevel', 'course.classRoom'])
                ->whereHas('payment', function ($query) use ($student) {
                    $query->where('student_id', $student->id)
                        ->where('status', 'success');
                })
                ->get();

                        return response()->json([
                'status'  => true,
                'message' => 'Purchased courses fetched successfully',
                'data'    => RecordedMaterialResource::collection($paymentItems),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch purchased courses',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $student = $request->user();

        if (!\Hash::check($request->current_password, $student->password)) {
            return response()->json([
                'status' => false,
                'errors' => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $student->password = \Hash::make($request->new_password);
        $student->save();

        return response()->json([
            'status' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    public function getTestimonials(Request $request)
    {
        try {
            /** @var Student $student */
            $student = $request->user();

            // Get all approved testimonials created by this student
            $testimonials = Testimonial::with(['classRoom', 'course.teacher', 'course.academicLevel'])
                ->where('created_by', $student->id)
                ->where('created_type', 'student')
                ->latest()
                ->get();
            
            $collection = TestimonialResource::collection($testimonials);
            $resolved = $collection->resolve();

            $additionalInfo = AdditionalInformation::where('type', 'students_testimonials')->first();
            $sectionTitle = $additionalInfo
                ? $additionalInfo->getLocalizationTitle()
                : __('app.students') . ' ' . __('app.testimonials');
            $sectionDescription = $additionalInfo
                ? $additionalInfo->getLocalizationDescription()
                : '';

            return response()->json([
                'status'  => true,
                'message' => 'Testimonials fetched successfully',
                'data'    => TestimonialResource::collection($testimonials),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch testimonials',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated student's payments by payment item (one record per item).
     * A payment with 2 items returns 2 records.
     */
    public function getPayments(Request $request)
    {
        try {
            /** @var Student $student */
            $student = $request->user();

            $paymentItems = PaymentItem::with(['payment', 'course.classRoom', 'installments'])
                ->whereHas('payment', function ($query) use ($student) {
                    $query->where('student_id', $student->id)
                        ->whereIn('status', ['success', 'failed']);
                })
                ->join('payments', 'payments.id', '=', 'payment_items.payment_id')
                ->orderByDesc('payments.paid_at')
                ->orderByDesc('payment_items.id')
                ->select('payment_items.*')
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Payments and installments fetched successfully',
                'data'    => StudentPaymentByItemResource::collection($paymentItems),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch payments',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if the student has unpaid installments (invoices) due in the next 2 days.
     */
    public function hasUnpaidInvoicesInNextTwoDays(Request $request)
    {
        try {
            /** @var Student $student */
            $student = $request->user();

            $today = Carbon::today();
            $twoDaysFromNow = Carbon::today()->addDays(2);

            $unpaidInstallments = Installment::with(['payment', 'paymentItem.course'])
                ->whereHas('payment', function ($query) use ($student) {
                    $query->where('student_id', $student->id)
                        ->where('status', 'success');
                })
                ->where('status', '!=', 'paid')
                ->whereBetween('due_date', [$today, $twoDaysFromNow])
                ->orderBy('due_date')
                ->get();

            $hasUnpaid = $unpaidInstallments->isNotEmpty();

            return response()->json([
                'status'  => true,
                'message' => 'Check completed successfully',
                'data'    => [
                    'has_unpaid_invoices_next_two_days' => $hasUnpaid,
                    'count'                             => $unpaidInstallments->count(),
                    'installments'                      => $unpaidInstallments->map(function ($i) {
                        $courseName = $i->paymentItem?->getLocalizationTitle() ?? '';
                        $dueDate = $i->due_date?->format('Y.m.d') ?? '';
                        $reminderMessage = __('messages.installment_reminder', [
                            'number' => $i->installment_number,
                            'course' => $courseName,
                            'date'   => $dueDate,
                        ]);
                        return [
                            'id'                 => $i->id,
                            'installment_number' => $i->installment_number,
                            'amount'             => (float) $i->amount,
                            'due_date'           => $dueDate,
                            'status'             => $i->status,
                            'course_name'        => $courseName,
                            'message'            => $reminderMessage,
                        ];
                    })->values(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to check unpaid invoices',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getNotifications(Request $request)
    {
        try {
            /** @var Student $student */
            $student = $request->user();

            $notifications = Notification::query()
                ->where('student_id', $student->id)
                ->with([
                    'model' => function (MorphTo $morphTo) {
                        $morphTo->morphWith([
                            Course::class => ['teacher'],
                        ]);
                    },
                ])
                ->latest()
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Notifications fetched successfully',
                'data' => NotificationResource::collection($notifications),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeTestimonial(Request $request)
    {
        try {
            /** @var Student $student */
            $student = $request->user();

            $validator = validator($request->all(), [
                'text'      => 'required|string',
                'rate'      => 'required|integer|min:0|max:5',
                'course_id' => 'nullable|exists:courses,id',
                'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            ], [
                'text.required'      => __('messages.testimonial_text_required'),
                'text.string'        => __('messages.testimonial_text_string'),
                'rate.required'      => __('messages.testimonial_rate_required'),
                'rate.integer'       => __('messages.testimonial_rate_integer'),
                'rate.min'           => __('messages.testimonial_rate_min'),
                'rate.max'           => __('messages.testimonial_rate_max'),
                'course_id.exists'   => __('messages.testimonial_course_exists'),
                'image.image'        => __('messages.testimonial_image_image'),
                'image.mimes'        => __('messages.testimonial_image_mimes'),
                'image.max'          => __('messages.testimonial_image_max'),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.validation_failed'),
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Get class_id from course when course_id is provided
            $classId = null;
            if (!empty($validated['course_id'])) {
                $course = Course::find($validated['course_id']);
                $classId = $course?->class_id;
            }

            if (
                ! empty($validated['course_id'])
                && ! Setting::getBool('allow_multiple_testimonials', false)
            ) {
                $alreadySubmitted = Testimonial::query()
                    ->where('created_by', $student->id)
                    ->where('created_type', 'student')
                    ->where('course_id', $validated['course_id'])
                    ->exists();

                if ($alreadySubmitted) {
                    return response()->json([
                        'status' => false,
                        'message' => __('messages.testimonial_already_exists_for_course'),
                    ], 422);
                }
            }

            // Create testimonial with student info (name from authenticated student)
            $testimonial = Testimonial::create([
                'name'         => $student->name,
                'text'         => $validated['text'],
                'rate'         => $validated['rate'],
                'class_id'     => $classId,
                'course_id'    => $validated['course_id'] ?? null,
                'status'       => 'pending', // Students can only create pending testimonials
                'created_by'   => $student->id,
                'created_type' => 'student',
            ]);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $testimonial->addMedia($request->file('image'))->toMediaCollection('testimonials');
            }

            // Load relationships for response
            $testimonial->load(['classRoom', 'course.teacher', 'course.academicLevel']);

            return response()->json([
                'status'  => true,
                'message' => 'Testimonial created successfully',
                'data'    => new TestimonialResource($testimonial),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.validation_failed'),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create testimonial',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
