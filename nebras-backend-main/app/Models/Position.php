<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get localized name based on current locale.
     */
    public function getLocalizationName(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->name : $this->name_en;
    }

    /**
     * Scope: only active positions.
     */
    public function scopeGetActive($query)
    {
        return $query->where('status', true);
    }
}
