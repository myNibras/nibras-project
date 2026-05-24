<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'name_en',
        'guard_name'
    ];

    public function getLocalizationName()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->attributes['name'] : $this->attributes['name_en'];
    }
}
