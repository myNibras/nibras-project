<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'title'                 => $this->getLocalizationTitle(),
            'short_description'     => $this->getLocalizationShortDescription(),
            'description'           => $this->getLocalizationDescription(),
            'duration'              => $this->getLocalizationDuration(),
            'price'                 => (new \App\Helpers\Helper)->formatNumber($this->price),
            'discount_price'        => ($this->discount_price) ? (new \App\Helpers\Helper)->formatNumber($this->discount_price) : null,
            'schedule'              => $this->getLocalizationSchedule(),
            'available_seats'       => $this->available_seats,
            'registered_students_count' => $this->registered_students_count,
            'final_available_seats' => $this->final_available_seats,
            'image'                 => $this->getImageAttribute(),
            'course_type'           => $this->course_type,
            'course_link'           => $this->course_link,
            "class"                 => $this->classRoom->getLocalizationName(),
            "class_id"              => $this->class_id,
            "slug"                  => $this->getLocalizationSlug(),
            "slug_ar"               => $this->slug,
            "slug_en"               => $this->slug_en,
            "payment_type"          => $this->payment_type,
            "semester_months"       => $this->semester_months,
            "monthly_amount"        => (new \App\Helpers\Helper)->formatNumber($this->monthly_amount),
            'teacher'               => new TeacherListResource($this->whenLoaded('teacher')),
            'semester'              => new SemesterResource($this->whenLoaded('semester')),
            'academic_level'        => new AcademicLevelResource($this->whenLoaded('academicLevel')),
            "curriculums"           => CurriculumResource::collection($this->whenLoaded('curriculums')),
        ];
    }
}
