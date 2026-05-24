<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class HomeSlider extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title',
        'title_en',
        'description',
        'description_en',
        'button_title',
        'button_title_en',
        'button_link',
        'button_link_en',
    ];

    protected $hidden = ['media'];

    protected $appends = ['image'];

    public function getLocalizationTitle()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->attributes['title'] : $this->attributes['title_en'];
    }

    public function getLocalizationDescription()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->attributes['description'] : $this->attributes['description_en'];
    }

    public function getLocalizationButtonTitle()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->attributes['button_title'] : $this->attributes['button_title_en'];
    }

    public function getLocalizationButtonLink()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->attributes['button_link'] : $this->attributes['button_link_en'];
    }
    
    public function getImageAttribute()
    {
        $photo = $this->getMedia('home_sliders')->first();
        if ($photo) {
            return $photo->getUrl();
        }
        return null;
    }
}
