<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'coupon_code',
        'discount_percentage',
        'expiry_date',
        'status',
        'type',
        'coupon_id',
        'new_students',
        'student_id',
        'number_of_coupons',
        'limit_usage',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'status' => 'boolean',
        'new_students' => 'boolean',
        'discount_percentage' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::deleting(function (Coupon $coupon) {
            if ($coupon->isForceDeleting()) {
                return;
            }
            $coupon->coupon_code = $coupon->coupon_code . '_' . strtotime('now') . '_DELETED';
            $coupon->saveQuietly();
        });
    }

    /**
     * Check if coupon is valid
     * 
     * @param \App\Models\Student|null $student The student trying to use the coupon
     * @return array Returns ['valid' => bool, 'message' => string]
     */
    public function isValid($student = null)
    {
        // Check status = true
        if (!$this->status) {
            return ['valid' => false, 'message' => trans('messages.coupon_inactive')];
        }

        // Check expiry date not coming (not expired)
        if ($this->expiry_date < now()->toDateString()) {
            return ['valid' => false, 'message' => trans('messages.coupon_expired')];
        }

        // For gift coupons, check the owner coupon's student
        if ($this->type === 'gift') {
            // If new_students = 1, check coupon created_at is before student created_at
            if ($this->new_students == 1) {
                if ($this->created_at >= $student->created_at) {
                    return ['valid' => false, 'message' => trans('messages.coupon_not_for_new_students')];
                }
            }
        }
        // For owner coupons, check if coupon is for a specific student
        elseif ($this->student_id) {
            if (!$student || $this->student_id != $student->id) {
                return ['valid' => false, 'message' => trans('messages.coupon_not_valid_for_account')];
            }

            // If new_students = 1, check coupon created_at is before student created_at
            if ($this->new_students == 1) {
                if ($this->created_at >= $student->created_at) {
                    return ['valid' => false, 'message' => trans('messages.coupon_not_for_new_students')];
                }
            }
        }

        // Check limit_usage
        if ($this->limit_usage !== null) {
            $usedCount = $this->payments()
                ->where('status', 'success')
                ->count();
            
            if ($usedCount >= $this->limit_usage) {
                return ['valid' => false, 'message' => trans('messages.coupon_usage_limit_reached')];
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Calculate discount amount from a price
     */
    public function calculateDiscount($price)
    {
        return ($price * $this->discount_percentage) / 100;
    }

    /**
     * Get payments that used this coupon
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the student that this coupon belongs to (if any)
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the owner coupon that this gift coupon belongs to (if any)
     */
    public function ownerCoupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    /**
     * Get all gift coupons created from this owner coupon
     */
    public function giftCoupons()
    {
        return $this->hasMany(Coupon::class, 'coupon_id');
    }

    /**
     * Check if coupon has been used in any payment (regardless of payment status)
     * 
     * @return bool
     */
    public function hasBeenUsed()
    {
        return $this->payments()->exists();
    }
}
