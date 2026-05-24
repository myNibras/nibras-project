<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'course_id',
        'payment_type',
        'quantity',
        'price',
        'total',
        'title', 
        'title_en',
        'short_description', 
        'short_description_en',
    ];

    /**
     * Get the payment that owns the item.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the course associated with this item.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function getLocalizationTitle()
    {
        return app()->getLocale() === 'ar' ? $this->title : $this->title_en;
    }

    public function getLocalizationShortDescription()
    {
        return app()->getLocale() === 'ar' ? $this->short_description : $this->short_description_en;
    }

    /**
     * Get the installments for this payment item.
     */
    public function installments()
    {
        return $this->hasMany(Installment::class, 'payment_item_id');
    }
}
