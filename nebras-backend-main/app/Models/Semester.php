<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'title_en',
        'type',
        'status',
    ];

    public const TYPE_SEMESTER_ONE = 1;
    public const TYPE_SEMESTER_TWO = 2;

    public static function getTypes(): array
    {
        return [
            self::TYPE_SEMESTER_ONE => __('app.semester one'),
            self::TYPE_SEMESTER_TWO => __('app.semester two'),
        ];
    }

    public function getTypeNameAttribute(): string
    {
        return self::getTypes()[$this->type] ?? '-';
    }

    public function getLocalizationTitle()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->attributes['title'] : $this->attributes['title_en'];
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public static function getActive(): ?self
    {
        return static::active()->first();
    }
}
