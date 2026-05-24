<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'course_id'         => $this->course_id,
            'payment_type'     => $this->payment_type,
            'quantity'          => $this->quantity,
            'price'             => $this->price,
            'total'             => $this->total,
            'title'             => $this->getLocalizationTitle(),
            'short_description' => $this->getLocalizationShortDescription(),
            'course'            => new CourseResource($this->whenLoaded('course')),
        ];
    }
}

