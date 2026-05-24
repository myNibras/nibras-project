<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Candidate extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'years_of_experience',
        'major_specialization',
    ];

    protected $hidden = ['media'];

    protected $appends = ['cv_url'];

    protected $casts = [
        'years_of_experience' => 'integer',
    ];

    /**
     * Get CV file URL if exists.
     */
    public function getCvUrlAttribute(): ?string
    {
        $media = $this->getMedia('cv')->first();
        return $media ? $media->getUrl() : null;
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cv')->singleFile();
    }
}
