<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getLocalizationTitle(),
            'small_description' => $this->getLocalizationSmallDescription(),
            'full_description' => $this->getLocalizationFullDescription(),
            'image' => $this->image,
            'expiry_date' => $this->expiry_date ? $this->expiry_date->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

