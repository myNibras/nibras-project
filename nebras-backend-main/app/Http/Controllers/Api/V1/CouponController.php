<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use App\Traits\Api\V1\ApiResponse;

class CouponController extends Controller
{
    use ApiResponse;

    /**
     * Add coupon to cart
     */
    public function addCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            
            // Fetch existing pending payment (cart) for this user
            $payment = Payment::with(['items.course'])
                ->where('student_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$payment || $payment->items->isEmpty()) {
                return $this->error(trans('messages.cart_empty'), 400);
            }

            // Find the coupon with owner relationship if it's gift
            $coupon = Coupon::with('ownerCoupon')
                ->where('coupon_code', $request->coupon_code)
                ->first();
            
            if (!$coupon) {
                return $this->error(trans('messages.invalid_coupon_code'), 400);
            }

            // Validate coupon with all conditions
            $validation = $coupon->isValid($user);
            if (!$validation['valid']) {
                return $this->error($validation['message'], 400);
            }

            // Check if user has already used this coupon
            $alreadyUsed = Payment::where('student_id', $user->id)
                ->where('coupon_id', $coupon->id)
                ->where('status', 'success')
                ->exists();
            
            if ($alreadyUsed) {
                return $this->error(trans('messages.coupon_already_used'), 400);
            }

            // Check if there's already a coupon applied
            if ($payment->coupon_id) {
                return $this->error(trans('messages.coupon_already_applied'), 400);
            }

            // Load coupon relationship
            $payment->load('coupon');
            
            // Update payment with coupon first
            $payment->coupon_id = $coupon->id;
            $payment->save();
            
            // Validate and update cart prices
            // This will update one-off items to use discount_price (via getFinalPrice)
            // and recalculate discount only on one-off items
            $payment->validateAndUpdateCartPrices();
            
            // Reload payment to get updated values
            $payment->refresh();
            $payment->load('coupon');

            return $this->success([
                'coupon_code' => $coupon->coupon_code,
                'discount_percentage' => $coupon->discount_percentage,
                'discount_amount' => $payment->discount_amount,
                'original_amount' => $payment->items->sum('total') + $payment->discount_amount,
                'final_amount' => $payment->amount,
            ], trans('messages.coupon_applied_successfully'));

        } catch (\Exception $e) {
            return $this->error(trans('messages.failed_to_apply_coupon'), 500, [$e->getMessage()]);
        }
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(Request $request)
    {
        // Set locale from request if provided
        if ($request->has('locale') && in_array($request->locale, ['en', 'ar'])) {
            app()->setLocale($request->locale);
        } elseif ($request->hasHeader('Accept-Language')) {
            $locale = substr($request->header('Accept-Language'), 0, 2);
            if (in_array($locale, ['en', 'ar'])) {
                app()->setLocale($locale);
            }
        }

        try {
            $user = Auth::user();
            
            // Fetch existing pending payment (cart) for this user
            $payment = Payment::with(['items.course'])
                ->where('student_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                return $this->error(trans('messages.cart_not_found'), 404);
            }

            // Check if there's a coupon applied
            if (!$payment->coupon_id) {
                return $this->error(trans('messages.no_coupon_applied'), 400);
            }

            // Remove coupon from payment
            $payment->coupon_id = null;
            $payment->save();
            
            // Validate and update cart prices
            // This will update one-off items to use discount_price (or price if no discount_price)
            // and recalculate totals without coupon discount
            $payment->validateAndUpdateCartPrices();
            
            // Reload payment to get updated values
            $payment->refresh();

            return $this->success([
                'original_amount' => $payment->items->sum('total'),
                'final_amount' => $payment->amount,
            ], trans('messages.coupon_removed_successfully'));

        } catch (\Exception $e) {
            return $this->error(trans('messages.failed_to_remove_coupon'), 500, [$e->getMessage()]);
        }
    }
}

