<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"              => $this->id,
            "name"            => $this->getLocalizationName(),
            "description"     => $this->getLocalizationDescription(),
            "video"           => $this->video,
            "video_url"       => $this->video_url,
            "video_embed_url" => $this->video_embed_url,
        ];
    }
}
