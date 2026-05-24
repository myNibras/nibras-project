<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One flat object per payment item. Payment with 2 items returns 2 records.
 */
class StudentPaymentByItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payment = $this->payment;
        $course = $this->course;
        $classRoom = $course?->classRoom;

        $paymentInvoiceLink = $payment->getFirstMediaUrl('invoices') ?: null;

        $installments = $this->whenLoaded('installments', function () use ($payment, $paymentInvoiceLink) {
            return collect($this->installments)->map(function ($installment) use ($payment, $paymentInvoiceLink) {
                $arr = (new InstallmentResource($installment))->toArray(request());
                if ($installment->installment_number === 1) {
                    $arr['invoice_link'] = $paymentInvoiceLink;
                    $arr['order_id'] = $payment->order_id;
                } else {
                    $installmentPayment = Payment::whereJsonContains('installment_ids', $installment->id)->first();
                    $arr['invoice_link'] = $installmentPayment?->getFirstMediaUrl('invoices') ?: null;
                    $arr['order_id'] = $installmentPayment?->order_id;
                }
                return $arr;
            })->values();
        });

        return [
            'order_id'    => $payment->order_id,
            'amount'      => (float) $payment->amount,
            'paid_at'     => $payment->paid_at?->format('Y.m.d'),
            'course_name' => $this->getLocalizationTitle(),
            'class_name'  => $classRoom ? $classRoom->getLocalizationName() : null,
            'invoice_link'=> $paymentInvoiceLink,
            'installments'=> $installments,
        ];
    }
}
