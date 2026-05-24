<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $course = $this->model instanceof Course ? $this->model : null;
        $teacher = $course?->teacher;

        return [
            'id' => $this->id,
            'message' => $this->getLocalizedMessage(),
            'course_name' => $course?->getLocalizationTitle(),
            'teacher_name' => $teacher?->getLocalizationName(),
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
