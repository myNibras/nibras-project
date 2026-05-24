<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    protected $table = "classes";
    protected $fillable = ['name', 'name_en', 'section', 'section_en', 'academic_level_id'];

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'class_id');
    }

    public function academicLevel()
    {
        return $this->belongsTo(AcademicLevel::class, 'academic_level_id');
    }

    public function getLocalizationName()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->attributes['name'] : $this->attributes['name_en'];
    }

    public function getLocalizationSection()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->attributes['section'] : $this->attributes['section_en'];
    }

    /**
     * Localized grade label: class name with section when present (dashboards, exports).
     */
    public function getLocalizationGradeLabel(): string
    {
        $name = (string) $this->getLocalizationName();
        $section = $this->getLocalizationSection();
        $section = $section !== null && $section !== '' ? (string) $section : '';

        if ($section !== '') {
            return trim($name . ' — ' . $section);
        }

        return $name !== '' ? $name : '—';
    }
}
