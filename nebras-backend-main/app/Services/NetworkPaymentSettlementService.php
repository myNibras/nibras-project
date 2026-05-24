<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Installment;
use App\Models\Notification;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NetworkPaymentSettlementService
{
    public function __construct(
        protected NetworkPaymentService $networkPayment,
    ) {}

    public function orderIsCaptured(array $result): bool
    {
        return strtolower((string) ($result['status'] ?? '')) === 'captured';
    }

    /**
     * Finalize payment after gateway reports CAPTURED (callback or reconciliation).
     */
    public function applyCapturedOrder(Payment $payment, array $result, bool $wasAlreadySuccessful = false): void
    {
        $payment->status = 'success';
        $payment->paid_at = Carbon::now();

        $this->persistOrderPayload($payment, $result);

        if ($payment->payment_method === 'network' && $payment->session_id) {
            try {
                $payment->token = $this->networkPayment->getToken($payment->session_id);
            } catch (\Throwable $e) {
                Log::warning('Network getToken failed after successful capture', [
                    'order_id' => $payment->order_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $payment->save();

        $this->syncInstallmentsAfterSuccess($payment);

        $this->maybeNotifyCourseRegistration($payment, $wasAlreadySuccessful);
    }

    /**
     * Non-captured result on return URL callback (mark failed, keep gateway payload).
     */
    public function applyNonCapturedCallbackResult(Payment $payment, array $result): void
    {
        $payment->status = 'failed';
        $this->persistOrderPayload($payment, $result);
        $payment->save();
    }

    private function persistOrderPayload(Payment $payment, array $result): void
    {
        $payment->response = $result;

        if (! isset($result['sourceOfFunds']['provided']['card'])) {
            return;
        }

        $card_info = $result['sourceOfFunds']['provided']['card'];
        $nameOnCard = $card_info['nameOnCard'] ?? '';
        $issuer = $card_info['issuer'] ?? '';
        if ($nameOnCard !== '') {
            $payment->name_on_card = $nameOnCard;
        }
        if ($issuer !== '') {
            $payment->bank_issuer = $issuer;
        }
    }

    private function syncInstallmentsAfterSuccess(Payment $payment): void
    {
        if (! empty($payment->installment_ids)) {
            Installment::whereIn('id', $payment->installment_ids)->update([
                'status' => 'paid',
                'paid_at' => Carbon::now(),
            ]);

            return;
        }

        if (! $payment->relationLoaded('items')) {
            $payment->load('items');
        }

        $hasMonthlyItems = $payment->items->contains(fn ($item) => $item->payment_type === 'monthly');
        if (! $hasMonthlyItems) {
            return;
        }

        foreach ($payment->items->where('payment_type', 'monthly') as $monthlyItem) {
            $firstInstallment = Installment::where('payment_id', $payment->id)
                ->where('payment_item_id', $monthlyItem->id)
                ->where('installment_number', 1)
                ->first();
            if ($firstInstallment) {
                $firstInstallment->status = 'paid';
                $firstInstallment->paid_at = Carbon::now();
                $firstInstallment->save();
            }
        }
    }

    private function maybeNotifyCourseRegistration(Payment $payment, bool $wasAlreadySuccessful): void
    {
        if (! empty($payment->installment_ids) || $wasAlreadySuccessful) {
            return;
        }

        if (! $payment->relationLoaded('items')) {
            $payment->load('items');
        }

        $this->createCourseRegistrationNotifications($payment);
    }

    private function createCourseRegistrationNotifications(Payment $payment): void
    {
        if ($payment->items->isEmpty()) {
            return;
        }

        foreach ($payment->items as $item) {
            if (! $item->course_id) {
                continue;
            }

            Notification::create([
                'student_id' => $payment->student_id,
                'message_ar' => 'تم تسجيلك بنجاح.',
                'message_en' => 'Registration completed successfully.',
                'is_read' => false,
                'model_type' => Course::class,
                'model_id' => $item->course_id,
            ]);
        }
    }
}
