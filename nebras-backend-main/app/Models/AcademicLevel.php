<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Str;

class AcademicLevel extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, Sluggable;

    public const QUOTE_ICON_COLORS = [
        '#1396FD',
        '#FD9A33',
        '#66D86E',
    ];

    public const DEFAULT_QUOTE_ICON_COLOR = '#1396FD';

    protected $fillable = [
        'title',
        'title_en',
        'description',
        'description_en',
        'slug',
        'slug_en',
        'quote_icon_color',
    ];

    protected $hidden = ['media'];

    protected $appends = ['image', 'thumbnail_male', 'thumbnail_female'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('academic_level')->singleFile();
        $this->addMediaCollection('academic_level_thumbnail_male')->singleFile();
        $this->addMediaCollection('academic_level_thumbnail_female')->singleFile();
    }

    public function sluggable(): array
    {
        return [
            // Arabic slug
            'slug' => [
                'source' => 'title',
                'method' => function ($string) {
                    return Str::of($string)->replace(' ', '-');
                },
                'onUpdate' => false
            ],
            // English slug
            'slug_en' => [
                'source' => 'title_en',
                'method' => function ($string) {
                    return Str::slug($string, '-');
                },
                'onUpdate' => false
            ]
        ];
    }

    public function getLocalizationTitle()
    {
        return app()->getLocale() === 'ar' ? $this->title : $this->title_en;
    }

    public function getLocalizationDescription()
    {
        return app()->getLocale() === 'ar' ? $this->description : $this->description_en;
    }

    public function getLocalizationSlug()
    {
        return app()->getLocale() === 'ar' ? $this->slug : $this->slug_en;
    }

    public function getImageAttribute()
    {
        $photo = $this->getMedia('academic_level')->first();
        if ($photo) {
            return $photo->getUrl();
        }
        return null;
    }

    public function getThumbnailMaleAttribute(): ?string
    {
        $media = $this->getMedia('academic_level_thumbnail_male')->first();
        return $media ? $media->getUrl() : null;
    }

    public function getThumbnailFemaleAttribute(): ?string
    {
        $media = $this->getMedia('academic_level_thumbnail_female')->first();
        return $media ? $media->getUrl() : null;
    }

    public function classes()
    {
        return $this->hasMany(ClassRoom::class, 'academic_level_id');
    }
}
