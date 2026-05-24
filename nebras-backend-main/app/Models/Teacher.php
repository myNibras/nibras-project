<?php

namespace App\Models;

use App\Helpers\YoutubeUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Teacher extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'name_en',
        'status',
        'position_id',
        'years_of_experience',
        'description',
        'description_en',
        'email',
        'password',
        'video_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'media',
    ];

    protected $appends = ['image', 'video', 'video_embed_url'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('teacher_videos')->singleFile();
    }

    protected $casts = [
        'status' => 'boolean',
        'years_of_experience' => 'integer',
        'password' => 'hashed',
    ];

    public function getLocalizationName()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->attributes['name'] : $this->attributes['name_en'];
    }

    public function scopeGetActive($query)
    {
        return $query->where('status', true);
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function getImageAttribute()
    {
        $photo = $this->getMedia('teachers')->first();
        if ($photo) {
            return $photo->getUrl();
        }
        return null;
    }

    public function getVideoAttribute()
    {
        $video = $this->getMedia('teacher_videos')->first();
        if ($video) {
            return $video->getUrl();
        }
        return null;
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        return YoutubeUrl::toEmbedUrl($this->attributes['video_url'] ?? null);
    }

    public function getLocalizationDescription(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->description : $this->description_en;
    }
}
