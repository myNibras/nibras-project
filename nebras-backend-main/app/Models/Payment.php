<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $hidden = ['media'];

    protected $appends = ['pdf'];

    protected $fillable = [
        'student_id',
        'session_id',
        'amount',
        'currency',
        'status',
        'order_id',
        'payment_method',
        'language',
        'send_invoice',
        'name_on_card',
        'bank_issuer',
        'response',
        'token',
        'coupon_id',
        'discount_amount',
        'paid_at',
        'installment_ids',
    ];

    protected $casts = [
        'send_invoice' => 'boolean',
        'response' => 'array',
        'discount_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'installment_ids' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function items()
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
    
    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    /**
     * Validate and update cart prices
     * Checks prices for every paymentItem and updates them to match current course prices
     * Recalculates coupon discount only on one-off items
     */
    public function validateAndUpdateCartPrices()
    {
        // Load items with courses if not already loaded
        if (!$this->relationLoaded('items')) {
            $this->load('items.course');
        }
        
        if (!$this->relationLoaded('coupon')) {
            $this->load('coupon');
        }

        $hasChanges = false;
        $oneOffItemsTotal = 0;
        $monthlyItemsTotal = 0;

        foreach ($this->items as $item) {
            if (!$item->course) {
                continue; // Skip items with non-existent courses
            }

            $course = $item->course;
            $expectedPrice = null;
            $expectedTotal = null;

            // Determine expected price based on payment type
            if ($item->payment_type === 'monthly') {
                // For monthly: always use monthly_amount
                $expectedPrice = $course->monthly_amount ?? 0;
                $expectedTotal = $expectedPrice * $item->quantity;
                $monthlyItemsTotal += $expectedTotal;
            } else {
                // For one-off: always use discount_price (via getFinalPrice) regardless of coupon status
                $expectedPrice = $course->getFinalPrice();
                $expectedTotal = $expectedPrice * $item->quantity;
                $oneOffItemsTotal += $expectedTotal;
            }

            // Update item if price or total doesn't match
            if ($item->price != $expectedPrice || $item->total != $expectedTotal) {
                $item->price = $expectedPrice;
                $item->total = $expectedTotal;
                $item->save();
                $hasChanges = true;
            }
        }

        // Always recalculate payment amount and coupon discount
        // This ensures totals are correct even if prices haven't changed
        $originalAmount = $oneOffItemsTotal + $monthlyItemsTotal;
        
        // Calculate coupon discount only on one-off items
        $discountAmount = 0;
        if ($this->coupon_id && $this->coupon && $oneOffItemsTotal > 0) {
            $discountAmount = $this->coupon->calculateDiscount($oneOffItemsTotal);
        }
        
        // Final amount = original amount - discount (discount only applies to one-off items)
        $finalAmount = $originalAmount - $discountAmount;
        
        // Update payment
        $this->amount = $finalAmount;
        $this->discount_amount = $discountAmount;
        $this->save();

        return $hasChanges;
    }

    /**
     * Update payment totals and discount amount
     * Recalculates the total amount and discount based on current items
     */
    public function updatePaymentTotals()
    {
        // Load items and coupon if not already loaded
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }
        
        if (!$this->relationLoaded('coupon')) {
            $this->load('coupon');
        }

        // Calculate totals by payment type
        $oneOffItemsTotal = 0;
        $monthlyItemsTotal = 0;

        foreach ($this->items as $item) {
            if ($item->payment_type === 'monthly') {
                $monthlyItemsTotal += $item->total;
            } else {
                $oneOffItemsTotal += $item->total;
            }
        }

        // Calculate original amount (sum of all items)
        $originalAmount = $oneOffItemsTotal + $monthlyItemsTotal;
        
        // Calculate coupon discount only on one-off items
        $discountAmount = 0;
        if ($this->coupon_id && $this->coupon && $oneOffItemsTotal > 0) {
            $discountAmount = $this->coupon->calculateDiscount($oneOffItemsTotal);
        }
        
        // Final amount = original amount - discount (discount only applies to one-off items)
        $finalAmount = $originalAmount - $discountAmount;
        
        // Update payment
        $this->amount = $finalAmount;
        $this->discount_amount = $discountAmount;
        $this->save();

        return [
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'one_off_total' => $oneOffItemsTotal,
            'monthly_total' => $monthlyItemsTotal,
        ];
    }
}