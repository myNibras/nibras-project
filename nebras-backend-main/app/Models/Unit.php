<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['curriculum_id', 'title', 'title_en', 'registered_students', 'link', 'open_in_new_tab'];

    protected $casts = [
        'registered_students' => 'boolean',
        'open_in_new_tab'     => 'boolean',
    ];

    public function getLocalizationTitle()
    {
        return app()->getLocale() === 'ar' ? $this->title : $this->title_en;
    }

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }
}
