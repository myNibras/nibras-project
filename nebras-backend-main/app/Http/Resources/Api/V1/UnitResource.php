<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isPurchased = (bool) $request->attributes->get('course_purchased', false);
        $effectiveLink = $isPurchased ? $this->link : ($this->registered_students ? null : $this->link);

        return [
            'id' => $this->id,
            'title' => $this->getLocalizationTitle(),
            'link' => $effectiveLink,
            'open_in_new_tab' => $effectiveLink ? (bool) $this->open_in_new_tab : null,
        ];
    }
}
