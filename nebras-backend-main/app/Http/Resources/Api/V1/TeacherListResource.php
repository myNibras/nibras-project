<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Course;
use App\Models\PaymentItem;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Returns: image, name, position, reviews, number_of_classes, number_of_students.
     * number_of_students is null when setting show_students_number_in_teacher is false.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $reviewsAvg = Testimonial::whereHas('course', function ($q) {
            $q->where('teacher_id', $this->id);
        })->where('status', 'approved')->avg('rate');
        $reviews = $reviewsAvg ? round($reviewsAvg, 1) : 0;

        $classesCount = Course::where('teacher_id', $this->id)
            ->distinct('class_id')
            ->count('class_id');

        $showStudentsNumber = Setting::getBool('show_students_number_in_teacher', true);
        $numberOfStudents = null;
        if ($showStudentsNumber) {
            $numberOfStudents = PaymentItem::whereHas('course', function ($q) {
                $q->where('teacher_id', $this->id);
            })
                ->whereHas('payment', function ($q) {
                    $q->where('status', 'success');
                })
                ->with('payment:id,student_id')
                ->get()
                ->pluck('payment.student_id')
                ->filter()
                ->unique()
                ->count();
        }

        $courses = $this->whenLoaded('courses', function () {
            return $this->courses
                ->map(fn ($course) => $course->getLocalizationTitle())
                ->unique()
                ->values()
                ->all();
        }, []);

        return [
            'id'                    => $this->id,
            'image'                 => $this->image,
            'name'                  => $this->getLocalizationName(),
            'description'           => $this->getLocalizationDescription(),
            'video'                 => $this->video,
            'video_url'             => $this->video_url,
            'video_embed_url'       => $this->video_embed_url,
            'position'              => $this->position ? $this->position->getLocalizationName() : null,
            'years_of_experience'   => (int) ($this->years_of_experience ?? 0),
            'reviews'               => $reviews,
            'number_of_classes'     => $classesCount,
            'number_of_students'    => $numberOfStudents,
            'courses'               => $courses,
        ];
    }
}
