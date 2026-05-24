<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TestimonialResource;
use App\Models\Testimonial;
use App\Models\AdditionalInformation;
use App\Traits\Api\V1\ApiResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of testimonials.
     * 
     * Optional filter: teacher_id - filters testimonials by the teacher of the course
     */
    public function index(Request $request)
    {
        try {
            $query = Testimonial::approved()
                ->with(['classRoom', 'course.teacher', 'course.academicLevel']);

            // Filter by teacher_id (through course relationship)
            if ($request->has('teacher_id') && !empty($request->teacher_id)) {
                $query->whereHas('course', function ($q) use ($request) {
                    $q->where('teacher_id', $request->teacher_id);
                });
            }

            $testimonials = $query->latest()->get();

            $collection = TestimonialResource::collection($testimonials);
            $resolved = $collection->resolve();

            $additionalInfo = AdditionalInformation::where('type', 'students_testimonials')->first();
            $sectionTitle = $additionalInfo
                ? $additionalInfo->getLocalizationTitle()
                : __('app.testimonials');
            $sectionDescription = $additionalInfo
                ? $additionalInfo->getLocalizationDescription()
                : '';

            return $this->success([
                'section_title'       => $sectionTitle,
                'section_description' => $sectionDescription,
                'data'                => $resolved['data'] ?? $resolved,
            ], 'Testimonials fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch testimonials', 500, [$e->getMessage()]);
        }
    }

    /**
     * Display the specified testimonial.
     */
    public function show($id)
    {
        try {
            $testimonial = Testimonial::with(['classRoom', 'course.teacher', 'course.academicLevel'])->find($id);
            
            if (!$testimonial) {
                return $this->error('Testimonial not found', 404);
            }

            return $this->success(
                new TestimonialResource($testimonial),
                'Testimonial details fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to fetch testimonial', 500, [$e->getMessage()]);
        }
    }
}
