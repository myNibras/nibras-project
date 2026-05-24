<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Testimonial extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'text',
        'rate',
        'class_id',
        'course_id',
        'status',
        'created_by',
        'created_type'
    ];

    protected $hidden = ['media'];

    protected $appends = ['image'];

    protected $casts = [
        'status' => 'string',
        'rate' => 'integer',
    ];

    /**
     * Scope a query to only include approved testimonials.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get the class room that the testimonial belongs to.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the course that the testimonial belongs to.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the courses that have the same grade level (class_id) as this testimonial.
     * Returns courses where class_id matches this testimonial's class_id.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'class_id', 'class_id');
    }

    /**
     * Get the creator of the testimonial (Student or Admin).
     */
    public function creator()
    {
        if (!$this->created_by || !$this->created_type) {
            return null;
        }

        $modelClass = $this->created_type === 'student' 
            ? Student::class 
            : ($this->created_type === 'admin' ? Admin::class : null);

        return $modelClass ? $modelClass::find($this->created_by) : null;
    }

    /**
     * Get the image attribute.
     * Priority: 1. Testimonial image, 2. Academic level thumbnail (male/female) from the testimonial's course by gender,
     *           3. Main academic level image if the gender-specific thumbnail is missing.
     */
    public function getImageAttribute()
    {
        $photo = $this->getMedia('testimonials')->first();
        if ($photo) {
            return $photo->getUrl();
        }

        if ($this->created_type !== 'student' || !$this->created_by) {
            return null;
        }

        $student = $this->creator();
        if (!$student || !$this->course_id) {
            return null;
        }

        $this->loadMissing('course.academicLevel');

        $course = $this->course;
        if (!$course || !$course->academic_level_id) {
            return null;
        }

        $academicLevel = $course->academicLevel;
        if (!$academicLevel) {
            return null;
        }

        // 0 = male, 1 = female (see Student model); avoid (int) null === 0
        $gender = $student->gender;
        $thumb = null;
        if ($gender === 0 || $gender === '0') {
            $thumb = $academicLevel->getMedia('academic_level_thumbnail_male')->first();
        } elseif ($gender === 1 || $gender === '1') {
            $thumb = $academicLevel->getMedia('academic_level_thumbnail_female')->first();
        }

        if ($thumb) {
            return $thumb->getUrl();
        }

        $fallback = $academicLevel->getMedia('academic_level')->first();

        return $fallback ? $fallback->getUrl() : null;
    }
}
