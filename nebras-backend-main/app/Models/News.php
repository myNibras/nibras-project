<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class News extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title_ar',
        'title_en',
        'small_description_ar',
        'small_description_en',
        'full_description_ar',
        'full_description_en',
        'expiry_date',
        'status',
    ];

    protected $hidden = ['media'];

    protected $appends = ['image'];

    protected $casts = [
        'status' => 'boolean',
        'expiry_date' => 'datetime',
    ];

    /**
     * Get the localized title based on current locale.
     */
    public function getLocalizationTitle()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
    }

    /**
     * Get the localized small description based on current locale.
     */
    public function getLocalizationSmallDescription()
    {
        return app()->getLocale() === 'ar' ? $this->small_description_ar : $this->small_description_en;
    }

    /**
     * Get the localized full description based on current locale.
     */
    public function getLocalizationFullDescription()
    {
        return app()->getLocale() === 'ar' ? $this->full_description_ar : $this->full_description_en;
    }

    /**
     * Get the image attribute.
     */
    public function getImageAttribute()
    {
        $image = $this->getMedia('news')->first();
        if ($image) {
            return $image->getUrl();
        }
        return null;
    }

    /**
     * Check if news is expired.
     */
    public function isExpired()
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Scope to get only active and non-expired news.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
            });
    }
}

