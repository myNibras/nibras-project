<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Calculate original amount from items (before discount)
        $originalAmount = $this->relationLoaded('items') 
            ? $this->items->sum('total') 
            : 0;
        
        // Get coupon information if coupon is applied
        $couponCode = null;
        $discountPercentage = null;
        $discountAmount = $this->discount_amount ?? 0;
        $finalAmount = $this->amount;
        
        if ($this->relationLoaded('coupon') && $this->coupon) {
            $couponCode = $this->coupon->coupon_code;
            $discountPercentage = $this->coupon->discount_percentage;
        }
        
        return [
            'payment_id'         => $this->id,
            'items'              => CartItemResource::collection($this->whenLoaded('items')),
            'original_amount'    => $originalAmount,
            'coupon_code'        => $couponCode,
            'discount_percentage'=> $discountPercentage,
            'discount_amount'    => $discountAmount,
            'final_amount'       => $finalAmount,
            'total_amount'       => $finalAmount, // Keep for backward compatibility
            'item_count'         => $this->relationLoaded('items') ? $this->items->count() : 0,
        ];
    }
}

