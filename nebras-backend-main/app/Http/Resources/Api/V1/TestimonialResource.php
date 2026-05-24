<?php

namespace App\Http\Resources\Api\V1;

use App\Models\AcademicLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing('course.academicLevel');

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'text'          => $this->text,
            'rate'          => $this->rate,
            'status'        => $this->status,
            'image'         => $this->image,
            'quote_icon_color' => $this->course?->academicLevel?->quote_icon_color
                ?? AcademicLevel::DEFAULT_QUOTE_ICON_COLOR,
            'course'        => $this->whenLoaded('course', function () {
                $courseData = [
                    'id'      => $this->course->id,
                    'title'   => $this->course->getLocalizationTitle(),
                ];
                
                if ($this->course->relationLoaded('teacher') && $this->course->teacher) {
                    $courseData['teacher'] = [
                        'id'   => $this->course->teacher->id,
                        'name' => $this->course->teacher->getLocalizationName(),
                    ];
                }
                
                return $courseData;
            }),
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

