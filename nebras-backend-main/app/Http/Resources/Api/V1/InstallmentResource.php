<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'payment_item_id'    => $this->payment_item_id,
            'installment_number' => $this->installment_number,
            'amount'             => (float) $this->amount,
            'due_date'           => $this->due_date?->format('Y.m.d'),
            'status'             => $this->status,
            'paid_at'            => $this->paid_at?->format('Y.m.d'),
        ];
    }
}
