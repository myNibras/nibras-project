<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdditionalInformation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'title',
        'title_en',
        'description',
        'description_en',
    ];

    /**
     * Get the localized title based on current locale.
     */
    public function getLocalizationTitle()
    {
        return app()->getLocale() === 'ar' ? $this->title : $this->title_en;
    }

    /**
     * Get the localized description based on current locale.
     */
    public function getLocalizationDescription()
    {
        return app()->getLocale() === 'ar' ? $this->description : $this->description_en;
    }
}
