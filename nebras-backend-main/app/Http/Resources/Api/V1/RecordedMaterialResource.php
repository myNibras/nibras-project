<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordedMaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // dd($this->course);
        return [
            'title'       => $this->getLocalizationTitle(),
            'slug'        => $this->course->getLocalizationSlug(),
            'course_id'   => $this->course->id,
            'class'       => $this->course->classRoom->getLocalizationName(),
            'schedule'    => $this->course->getLocalizationSchedule(),
            'duration'    => $this->course->getLocalizationDuration(),
            'teacher'     => new TeacherResource($this->course->teacher),
            'image'       => $this->course->image,
            'course_type' => $this->course->course_type,
            'course_link' => $this->course->course_link,
            'semester'    => $this->course->semester?->type_name,
            'available_seats' => $this->course->available_seats,
            'registered_students_count' => $this->course->registered_students_count,
            'final_available_seats' => $this->course->final_available_seats
        ];
    }
}
