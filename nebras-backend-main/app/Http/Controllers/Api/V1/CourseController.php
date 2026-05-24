<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PaymentItem;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use App\Models\AcademicLevel;
use App\Traits\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\CourseResource;
use Illuminate\Support\Arr;

class CourseController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $query = Course::with(['semester', 'academicLevel', 'teacher', 'curriculums.units'])
                ->where("status", 1)
                ->whereHas('semester', function ($q) {
                    $q->where('status', true);
                });

            // Detect locale
            $locale = app()->getLocale();
            $isArabic = $locale === 'ar';

            // Filter by academic level slug
            if ($request->has('academic_level_slug') && !empty($request->academic_level_slug)) {
                $slugField = $isArabic ? 'slug' : 'slug_en';

                $query->whereHas('academicLevel', function ($q) use ($request, $slugField) {
                    $q->where($slugField, $request->academic_level_slug);
                });
            }

            // Filter by teacher id
            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }

            // Filter by course ids (supports course_id[], comma list, or single id) — same style as teachers API
            $courseIds = $this->parseIdsFilter($request->input('course_id'));
            if (! empty($courseIds)) {
                $query->whereIn('id', $courseIds);
            }

            // Filter by class / grade ids
            $classIds = $this->parseIdsFilter($request->input('class_id'));
            if (! empty($classIds)) {
                $query->whereIn('class_id', $classIds);
            }

            
            $courses = $query->latest()->get();


            return response()->json([
                'status'  => true,
                'message' => 'Courses fetched successfully',
                'data'    => CourseResource::collection($courses),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch courses',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $course = Course::with([
                'semester',
                'academicLevel',
                'teacher',
                'curriculums.units'
            ])->find($id);

            if (!$course) {
                return $this->error('Course not found', 404);
            }

            $courseData = $this->enrichCourseDetails($request, $course);

            // Fetch 3 random courses excluding the current one
            $relatedCourses = Course::where('id', '!=', $course->id)
                ->with([
                    'semester',
                    'academicLevel',
                    'teacher',
                    'curriculums.units'
                ])
                ->inRandomOrder()
                ->where("status", 1)
                ->limit(3)
                ->get();

            return $this->success([
                'course' => $courseData,
                'related_courses' => CourseResource::collection($relatedCourses),
            ], 'Course fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch course', 500, [$e->getMessage()]);
        }
    }


    public function showBySlug(Request $request, $academicLevelSlug, $courseSlug, $courseId)
    {
        try {
            $locale = app()->getLocale();
            $isArabic = $locale === 'ar';

            // Academic Level by slug
            $academicLevel = AcademicLevel::where($isArabic ? 'slug' : 'slug_en', $academicLevelSlug)->first();

            if (!$academicLevel) {
                return $this->error('Academic Level not found', 404);
            }

            // Course by slug
            $course = Course::with([
                    'teacher',
                    'semester',
                    'academicLevel',
                    'curriculums.units',
                ])
                ->where('academic_level_id', $academicLevel->id)
                ->where('id', $courseId)
                ->where($isArabic ? 'slug' : 'slug_en', $courseSlug)
                ->where("status", 1)
                ->first();

            if (!$course) {
                return $this->error('Course not found', 404);
            }

            $courseData = $this->enrichCourseDetails($request, $course);

            // Fetch 3 random courses excluding the current one
            $relatedCourses = Course::where('id', '!=', $course->id)
                ->with([
                    'semester',
                    'academicLevel',
                    'teacher',
                    'curriculums.units'
                ])
                ->inRandomOrder()
                ->where("status", 1)
                ->limit(3)
                ->get();

            return $this->success([
                'course' => $courseData,
                'related_courses' => CourseResource::collection($relatedCourses),
            ], 'Course fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch course', 500, [$e->getMessage()]);
        }
    }

    /**
     * Enrich course data with: purchased status, course_link (only if purchased), rating from approved testimonials,
     * and allow_send_testimonial (whether the auth student may submit another testimonial for this course).
     */
    private function enrichCourseDetails(Request $request, Course $course): array
    {
        $allowMultipleTestimonials = Setting::getBool('allow_multiple_testimonials', false);

        $hasPurchased = false;
        $allowSendTestimonial = false;
        // Students authenticate via Sanctum on the `api` guard (see config/auth.php). `$request->user()`
        // without arguments uses the default `web` guard (admins), so it is always null for API tokens.
        $student = $request->user('api');

        if ($student) {
            $hasPurchased = PaymentItem::where('course_id', $course->id)
                ->whereHas('payment', fn ($q) => $q->where('student_id', $student->id)->where('status', 'success'))
                ->exists();

            if ($allowMultipleTestimonials) {
                $allowSendTestimonial = true;
            } else {
                $alreadySubmitted = Testimonial::query()
                    ->where('created_by', $student->id)
                    ->where('created_type', 'student')
                    ->where('course_id', $course->id)
                    ->exists();
                $allowSendTestimonial = ! $alreadySubmitted;
            }
        }

        // Pass purchased context down to UnitResource via request.
        $request->attributes->set('course_purchased', $hasPurchased);

        $courseData = (new CourseResource($course))->toArray($request);

        $rating = Testimonial::where('course_id', $course->id)
            ->where('status', 'approved')
            ->avg('rate');

        $courseData['course_link'] = $hasPurchased ? ($course->course_link ?? null) : null;
        $courseData['rating'] = $rating !== null ? round((float) $rating, 2) : null;
        $courseData['purchased'] = $hasPurchased;
        $courseData['allow_send_testimonial'] = $allowSendTestimonial;

        return $courseData;
    }

    /**
     * Normalize ids from single value, comma-separated string, or array (e.g. course_id[]=1&course_id[]=2).
     *
     * @return array<int>
     */
    private function parseIdsFilter(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (! is_array($value)) {
            $value = [$value];
        }

        $ids = array_map(static fn ($id) => (int) $id, Arr::flatten($value));
        $ids = array_values(array_unique(array_filter($ids, static fn ($id) => $id > 0)));

        return $ids;
    }

    public function relatedCourses(){
        // Fetch 3 random courses excluding the current one
        $relatedCourses = Course::with([
                'semester',
                'academicLevel',
                'teacher',
                'curriculums.units'
            ])
            ->inRandomOrder()
            ->where("status", 1)
            ->limit(3)
            ->get();

        return $this->success([
            'data' => CourseResource::collection($relatedCourses),
        ], 'Course fetched successfully');
    }
}
