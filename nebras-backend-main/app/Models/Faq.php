<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'question',
        'question_en',
        'answer',
        'answer_en',
        'status',
        'order',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get localized question based on current locale.
     */
    public function getLocalizationQuestion(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->question : $this->question_en;
    }

    /**
     * Get localized answer based on current locale.
     */
    public function getLocalizationAnswer(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->answer : $this->answer_en;
    }

    /**
     * Scope a query to only include active (status = true) FAQs.
     */
    public function scopeGetActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to order FAQs by manual `order` (NULLs last), then id.
     */
    public function scopeOrderedAsc($query)
    {
        return $query
            ->orderByRaw('`order` IS NULL')
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc');
    }
}
