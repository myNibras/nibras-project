<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Installment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'payment_item_id',
        'installment_number',
        'amount',
        'due_date',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function paymentItem()
    {
        return $this->belongsTo(PaymentItem::class);
    }
    public function getReminderMessageAttribute()
    {
        $courseName = $this->paymentItem ? $this->paymentItem->title : '';
        $date = $this->due_date ? $this->due_date->format('Y-m-d') : '';
        
        return "تذكير بالدفعة المستحقة {$this->installment_number} لمادة {$courseName} بتاريخ {$date}";
    }
}
