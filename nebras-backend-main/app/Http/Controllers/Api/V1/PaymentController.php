<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Course;
use App\Models\Installment;
use App\Services\NetworkPaymentService;
use App\Services\NetworkPaymentSettlementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\CartResource;
use Carbon\Carbon;

class PaymentController extends Controller
{
    use ApiResponse;

    protected $networkPayment;

    protected NetworkPaymentSettlementService $networkPaymentSettlement;

    public function __construct(
        NetworkPaymentService $networkPayment,
        NetworkPaymentSettlementService $networkPaymentSettlement,
    ) {
        $this->networkPayment = $networkPayment;
        $this->networkPaymentSettlement = $networkPaymentSettlement;
    }

    /**
     * Create payment session
     */
    public function create(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string|in:network',
        ]);

        try {
            $user = Auth::user();
            
            // Fetch existing pending payment (cart) for this user
            $payment = Payment::with(['items.course'])
                ->where('student_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                return $this->error('Cart is empty. Please add items to cart first.', 400);
            }

            // Check and remove items with non-existent courses
            $itemsToRemove = [];
            $newTotal = 0;

            foreach ($payment->items as $item) {
                if (!$item->course) {
                    $itemsToRemove[] = $item->id;
                } else {
                    $newTotal += $item->total;
                }
            }

            // Remove items with non-existent courses
            if (!empty($itemsToRemove)) {
                PaymentItem::whereIn('id', $itemsToRemove)->delete();
                $payment->refresh();
                $payment->load(['items.course', 'coupon']);
            }

            // Check if cart is empty after cleanup
            if ($payment->items->isEmpty()) {
                return $this->error('Cart is empty. Please add items to cart first.', 400);
            }

            // Validate and update cart prices to ensure totals are correct
            $payment->validateAndUpdateCartPrices();
            
            // Update payment method
            $payment->payment_method = $request->payment_method;
            
            // Generate order_id if not exists
            if (!$payment->order_id) {
                $payment->order_id = 'N' . $user->id . time();
            }
            
            // Create installments for monthly payments when creating payment session
            // Check if any item has monthly payment type
            $hasMonthlyPayment = $payment->items->contains(function ($item) {
                return $item->payment_type === 'monthly';
            });
            
            if ($hasMonthlyPayment) {
                $existingInstallments = Installment::where('payment_id', $payment->id)->count();
                if ($existingInstallments === 0 && $payment->items->isNotEmpty()) {
                    // Create installments for each monthly payment item
                    $monthlyItems = $payment->items->where('payment_type', 'monthly');
                    foreach ($monthlyItems as $monthlyItem) {
                        if ($monthlyItem->course) {
                            $this->createInstallments($payment, $monthlyItem);
                        }
                    }
                }
            }
            
            $payment->save();

            // Build description from cart items
            $locale = app()->getLocale();
            $itemTitles = $payment->items->map(function ($item) use ($locale) {
                return $locale === 'ar' ? $item->title : $item->title_en;
            })->toArray();
            
            $description = implode(', ', $itemTitles);

            
            // Generate session using your service
            $sessionId = $this->networkPayment->createSession($payment, $description);

            $payment->update([
                'session_id' => $sessionId
            ]);

            return $this->success([
                'payment_id' => $payment->id,
                'session_id' => $sessionId,
            ], 'Payment session created successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to create payment session', 500, [$e->getMessage()]);
        }
    }

    /**
     * Create payment session for installments (pay unpaid installments).
     */
    public function createInstallmentSession(Request $request)
    {
        $request->validate([
            'payment_method'   => 'required|string|in:network',
            'installment_ids'   => 'required|array',
            'installment_ids.*' => 'integer|exists:installments,id',
        ]);

        try {
            $user = Auth::user();
            $installmentIds = array_values(array_unique($request->installment_ids));

            if (empty($installmentIds)) {
                return $this->error('At least one installment is required.', 400);
            }

            // Load installments that belong to this student (via payment) and are unpaid
            $installments = Installment::with(['payment', 'paymentItem.course'])
                ->whereHas('payment', function ($query) use ($user) {
                    $query->where('student_id', $user->id)->where('status', 'success');
                })
                ->where('status', '!=', 'paid')
                ->whereIn('id', $installmentIds)
                ->get();

            if ($installments->count() !== count($installmentIds)) {
                $foundIds = $installments->pluck('id')->toArray();
                $invalid = array_diff($installmentIds, $foundIds);
                return $this->error('Some installments are invalid or already paid: ' . implode(', ', $invalid), 400);
            }

            foreach ($installments as $installment) {
                if (! $installment->paymentItem) {
                    return $this->error('Installment #' . $installment->id . ' is not linked to a course line item.', 400);
                }
            }

            $totalAmount = $installments->sum('amount');
            if ($totalAmount <= 0) {
                return $this->error('Total amount must be greater than zero.', 400);
            }

            $orderId = 'N' . $user->id . time();

            $result = DB::transaction(function () use ($request, $user, $installments, $totalAmount, $orderId) {
                $payment = Payment::create([
                    'student_id'     => $user->id,
                    'amount'         => round((float) $totalAmount, 2),
                    'currency'       => 'USD',
                    'status'         => 'pending',
                    'order_id'       => $orderId,
                    'payment_method' => $request->payment_method,
                    'language'       => app()->getLocale(),
                    'installment_ids'=> $installments->pluck('id')->values()->toArray(),
                ]);

                // Line items grouped by original cart line (same course/monthly plan)
                foreach ($installments->groupBy('payment_item_id') as $rows) {
                    /** @var \App\Models\PaymentItem $origItem */
                    $origItem = $rows->first()->paymentItem;
                    $lineTotal = round((float) $rows->sum('amount'), 2);

                    PaymentItem::create([
                        'payment_id'           => $payment->id,
                        'course_id'            => $origItem->course_id,
                        'payment_type'         => $origItem->payment_type ?? 'monthly',
                        'quantity'             => 1,
                        'price'                => $lineTotal,
                        'total'                => $lineTotal,
                        'title'                => $origItem->title,
                        'title_en'             => $origItem->title_en,
                        'short_description'    => $origItem->short_description,
                        'short_description_en' => $origItem->short_description_en,
                    ]);
                }

                $descriptions = $installments->map(function ($i) {
                    $courseName = $i->paymentItem?->getLocalizationTitle() ?? 'Installment';
                    return "{$courseName} - #{$i->installment_number}";
                })->toArray();
                $description = implode(', ', $descriptions);

                $sessionId = $this->networkPayment->createSession($payment, $description);

                $payment->update(['session_id' => $sessionId]);

                return [
                    'payment_id' => $payment->id,
                    'session_id' => $sessionId,
                ];
            });

            return $this->success($result, 'Installment payment session created successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to create installment payment session', 500, [$e->getMessage()]);
        }
    }

    public function callback(Request $request){
        $trans_id = $request->query('trans_id');
        $payment = Payment::with(['items.course.academicLevel'])->where("order_id", $trans_id)->first();
        
        if(!$payment){
            return $this->redirectWithStatus("ar", 'failed', "Something wrong, please try again");
        }
        
        $lang = $payment->language ?? 'ar';
        $path = null;
        
        // Installment payment: redirect to profile/payments
        if (!empty($payment->installment_ids)) {
            $path = '/profile/payments';
        } else {
            // Cart payment: path from first item
            $firstItem = $payment->items->first();
            if($firstItem && $firstItem->course){
                if($lang == "ar"){
                    $path = $firstItem->course->slug."/".$firstItem->course->academicLevel->slug;
                } else {
                    $path = $firstItem->course->slug_en."/".$firstItem->course->academicLevel->slug_en;
                }
            }
        }
        
        try {
            $wasAlreadySuccessful = $payment->status === 'success';
            $result = $this->networkPayment->getOrderDetails($trans_id);

            if ($this->networkPaymentSettlement->orderIsCaptured($result)) {
                $this->networkPaymentSettlement->applyCapturedOrder($payment, $result, $wasAlreadySuccessful);
            } else {
                $this->networkPaymentSettlement->applyNonCapturedCallbackResult($payment, $result);
            }
            
            return $this->redirectWithStatus($lang, 'success', null);
            
        } catch (\Throwable $e) {
            return $this->redirectWithStatus($lang, 'failed', $e->getMessage(), $path);
        }
    }


    /**
     * Get cart items
     */
    public function getCart()
    {    
        try {
            $user = Auth::user();
            
            // Get pending payment (cart) for this user
            $payment = Payment::with(['items.course.academicLevel', 'items.course.teacher', 'items.course.semester', 'coupon'])
                ->where('student_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                return $this->success([
                    'payment_id' => null,
                    'items' => [],
                    'original_amount' => 0,
                    'coupon_code' => null,
                    'discount_percentage' => null,
                    'discount_amount' => 0,
                    'final_amount' => 0,
                    'total_amount' => 0,
                    'item_count' => 0,
                ], 'Cart is empty');
            }

            // Check and remove items with non-existent courses
            $itemsToRemove = [];
            $newTotal = 0;

            foreach ($payment->items as $item) {
                if (!$item->course) {
                    // Course doesn't exist, mark for removal
                    $itemsToRemove[] = $item->id;
                } else {
                    // Course exists, add to new total
                    $newTotal += $item->total;
                }
            }

            // Remove items with non-existent courses
            if (!empty($itemsToRemove)) {
                PaymentItem::whereIn('id', $itemsToRemove)->delete();
                
                // Reload payment with items and coupon
                $payment->refresh();
                $payment->load(['items.course.academicLevel', 'items.course.teacher', 'items.course.semester', 'coupon']);
            }

            // If all items were removed, return empty cart
            if ($payment->items->isEmpty()) {
                return $this->success([
                    'payment_id' => null,
                    'items' => [],
                    'original_amount' => 0,
                    'coupon_code' => null,
                    'discount_percentage' => null,
                    'discount_amount' => 0,
                    'final_amount' => 0,
                    'total_amount' => 0,
                    'item_count' => 0,
                ], 'Cart is empty');
            }

            // Validate and update cart prices
            $payment->validateAndUpdateCartPrices();
            
            // Reload payment to get updated values
            $payment->refresh();
            $payment->load(['items.course.academicLevel', 'items.course.teacher', 'items.course.semester', 'coupon']);

            return $this->success(
                new CartResource($payment),
                'Cart fetched successfully'
            );

        } catch (\Exception $e) {
            return $this->error('Failed to fetch cart', 500, [$e->getMessage()]);
        }
    }

    /**
     * Add course to cart
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'course_id'      => 'required|exists:courses,id',
            'payment_type'   => 'required|string|in:one-off,monthly',
        ]);

        try {
            $user = Auth::user();
            $course = Course::findOrFail($request->course_id);
            $paymentType = $request->payment_type;

            // Validate that the course supports the selected payment type
            if ($course->payment_type !== 'both' && $course->payment_type !== $paymentType) {
                return $this->error(
                    "This course does not support {$paymentType} payment. Available payment type: {$course->payment_type}",
                    400
                );
            }

            // Check if there's an existing pending payment for this user
            $payment = Payment::with('coupon')
                ->where('student_id', $user->id)
                ->where('status', 'pending')
                ->first();

            // Calculate price based on payment type
            if ($paymentType === 'monthly') {
                // Validate monthly payment fields
                if (empty($course->monthly_amount) || $course->monthly_amount <= 0) {
                    return $this->error('Monthly amount is not set for this course', 400);
                }
                if (empty($course->semester_months) || $course->semester_months <= 0) {
                    return $this->error('Semester months is not set for this course', 400);
                }
                $itemPrice = $course->monthly_amount;
            } else {
                // One-off payment: always use discount_price (via getFinalPrice) regardless of coupon status
                $itemPrice = $course->getFinalPrice();
            }

            // If no pending payment exists, create a new one
            if (!$payment) {
                $order_id = 'N' . $user->id . time();
                $payment = Payment::create([
                    'student_id'     => $user->id,
                    'payment_method' => "network",
                    'status'         => 'pending',
                    'amount'         => $itemPrice,
                    'order_id'       => $order_id,
                    'language'       => app()->getLocale()
                ]);
            } else {
                // Check if the course is already in the cart
                $existingItem = PaymentItem::where('payment_id', $payment->id)
                    ->where('course_id', $course->id)
                    ->first();

                if ($existingItem) {
                    return $this->error('Course already in cart', 400);
                }
            }
            
            // Add course as payment item with payment_type
            PaymentItem::create([
                'payment_id'                => $payment->id,
                'course_id'                 => $course->id,
                'payment_type'              => $paymentType,
                'quantity'                  => 1,
                'price'                     => $itemPrice,
                'total'                     => $itemPrice,
                'title'                     => $course->title,
                'title_en'                  => $course->title_en,
                'short_description'         => $course->short_description,
                'short_description_en'      => $course->short_description_en,
            ]);

            // Validate and update cart prices to recalculate totals and coupon discount
            $payment->validateAndUpdateCartPrices();

            return $this->success(null, 'Add to cart successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to add to cart', 500, [$e->getMessage()]);
        }
    }

    /**
     * Remove course from cart
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        try {
            $user = Auth::user();

            // Find pending payment (cart) for this user
            $payment = Payment::with('items')
                ->where('student_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                return $this->error('Cart not found', 404);
            }

            // Find the item to remove
            $item = PaymentItem::where('payment_id', $payment->id)
                ->where('course_id', $request->course_id)
                ->first();

            if (!$item) {
                return $this->error('Course not found in cart', 404);
            }

            // Store item payment type before removal
            $itemPaymentType = $item->payment_type;

            // Remove the item
            $item->delete();
            
            // Reload payment to get updated items
            $payment->refresh();
            $payment->load('items.course', 'coupon');
            
            // Check if cart is now empty
            if ($payment->items->isEmpty()) {
                // Remove all installments if the removed item was a monthly payment
                if ($itemPaymentType === 'monthly') {
                    Installment::where('payment_id', $payment->id)->delete();
                }
                
                // Reset payment totals
                $payment->amount = 0;
                $payment->discount_amount = 0;
                $payment->save();
                
                return $this->success([
                    'message' => 'Item removed from cart. Cart is now empty.',
                    'cart_empty' => true,
                ], 'Item removed from cart successfully');
            }

            // Update payment totals and discount
            $payment->validateAndUpdateCartPrices();

            return $this->success(null, 'Item removed from cart successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to remove from cart', 500, [$e->getMessage()]);
        }

    }

    private function createInstallments(Payment $payment, PaymentItem $paymentItem)
    {
        if (!$paymentItem->course) {
            return;
        }

        $installments = [];
        $course = $paymentItem->course;
        $semesterMonths = $course->semester_months;
        $monthlyAmount = $course->monthly_amount;
        
        // First installment due date is today
        $firstDueDate = Carbon::now();
        
        for ($i = 1; $i <= $semesterMonths; $i++) {
            $installments[] = [
                'payment_id' => $payment->id,
                'payment_item_id' => $paymentItem->id,
                'installment_number' => $i,
                'amount' => $monthlyAmount,
                'due_date' => $firstDueDate->copy()->addMonths($i - 1)->format('Y-m-d'),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        Installment::insert($installments);
    }

    private function redirectWithStatus(string $locale, string $status, string $message = null, string $path = null)
    {
        $baseUrl = rtrim(env('APP_URL_FRONTEND'), '/');

        $url = "{$baseUrl}/{$locale}{$path}?payment={$status}";
        if ($message) {
            $url .= '&message=' . urlencode($message);
        }
        return redirect($url);
    }
}
