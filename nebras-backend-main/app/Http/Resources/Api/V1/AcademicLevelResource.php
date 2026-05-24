<?php

namespace App\Http\Resources\Api\V1;

use App\Models\AcademicLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicLevelResource extends JsonResource
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
            'title'             => $this->getLocalizationTitle(),
            'description'       => $this->getLocalizationDescription(),
            'image'             => $this->getImageAttribute(),
            'thumbnail_male'    => $this->thumbnail_male,
            'thumbnail_female'  => $this->thumbnail_female,
            'quote_icon_color'  => $this->quote_icon_color ?? AcademicLevel::DEFAULT_QUOTE_ICON_COLOR,
            'slug'              => $this->getLocalizationSlug(),
            'slug_ar'           => $this->slug,
            'slug_en'           => $this->slug_en,
        ];
    }
}
