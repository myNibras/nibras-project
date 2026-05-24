<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Partner extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name_ar',
        'name_en',
        'status',
    ];

    protected $hidden = ['media'];

    protected $appends = ['logo'];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the localized name based on current locale.
     */
    public function getLocalizationName()
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * Get the logo attribute.
     */
    public function getLogoAttribute()
    {
        $logo = $this->getMedia('partners')->first();
        if ($logo) {
            return $logo->getUrl();
        }
        return null;
    }
}
