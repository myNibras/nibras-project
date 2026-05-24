<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    protected $fillable = ['course_id', 'title', 'title_en'];

    public function getLocalizationTitle()
    {
        return app()->getLocale() === 'ar' ? $this->title : $this->title_en;
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
