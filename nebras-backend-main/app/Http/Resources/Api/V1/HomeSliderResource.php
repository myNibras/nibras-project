<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeSliderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->getLocalizationTitle(),
            'description'     => $this->getLocalizationDescription(),
            'button_title'    => $this->getLocalizationButtonTitle(),
            'button_link'     => $this->getLocalizationButtonLink(),
            'is_login_btn'    => ($this->button_link && $this->button_link_en) ? false : true,
            'image'           => $this->getImageAttribute()
        ];
    }
}
