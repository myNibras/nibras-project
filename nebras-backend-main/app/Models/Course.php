<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Str;

class Course extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, Sluggable;

    private ?int $registeredStudentsCountCache = null;
    private ?int $finalAvailableSeatsCache = null;

    protected $fillable = [
        'semester_id', 'academic_level_id', 'teacher_id',
        'title', 'title_en', 'class_id',
        'course_type', 'course_link',
        'short_description', 'short_description_en',
        'description', 'description_en',
        'price', 'discount_price',
        'payment_type', 'semester_months', 'monthly_amount',
        'duration', 'duration_en',
        'schedule', 'schedule_en',
        'available_seats',
        'slug', 'slug_en', 'status'
    ];

    protected $hidden = ['media'];

    protected $appends = ['image'];

    protected $casts = [
        'available_seats' => 'integer',
    ];

    public function sluggable(): array
    {
        return [
            // Arabic slug
            'slug' => [
                'source' => 'title',
                'unique' => false,
                'method' => function ($string) {
                    return Str::of($string)->replace(' ', '-');
                },
                'onUpdate' => true
            ],
            // English slug
            'slug_en' => [
                'source' => 'title_en',
                'unique' => false,
                'method' => function ($string) {
                    return Str::slug($string, '-');
                },
                'onUpdate' => true
            ]
        ];
    }

    public function getLocalizationSlug()
    {
        return app()->getLocale() === 'ar' ? $this->slug : $this->slug_en;
    }

    public function getLocalizationTitle()
    {
        return app()->getLocale() === 'ar' ? $this->title : $this->title_en;
    }

    /**
     * Course title with teacher name (for selects and listings).
     */
    public function getLocalizationTitleWithTeacher(): string
    {
        $title = $this->getLocalizationTitle();
        $this->loadMissing('teacher');
        if ($this->teacher) {
            return $title . ' — ' . $this->teacher->getLocalizationName();
        }

        return $title;
    }

    public function getLocalizationShortDescription()
    {
        return app()->getLocale() === 'ar' ? $this->short_description : $this->short_description_en;
    }

    public function getLocalizationDuration()
    {
        return app()->getLocale() === 'ar' ? $this->duration : $this->duration_en;
    }

    public function getLocalizationSchedule()
    {
        return app()->getLocale() === 'ar' ? $this->schedule : $this->schedule_en;
    }

    public function getLocalizationDescription()
    {
        return app()->getLocale() === 'ar' ? $this->description : $this->description_en;
    }

    public function getImageAttribute()
    {
        $photo = $this->getMedia('courses')->first();
        if ($photo) {
            return $photo->getUrl();
        }
        return null;
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function academicLevel()
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function curriculums()
    {
        return $this->hasMany(Curriculum::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function paymentItems()
    {
        return $this->hasMany(PaymentItem::class, 'course_id');
    }

    /**
     * Count unique students registered successfully for this course.
     */
    public function registeredStudentsCount(): int
    {
        if ($this->registeredStudentsCountCache !== null) {
            return $this->registeredStudentsCountCache;
        }

        $this->registeredStudentsCountCache = PaymentItem::query()
            ->join('payments', 'payments.id', '=', 'payment_items.payment_id')
            ->where('payment_items.course_id', $this->id)
            ->where('payments.status', 'success')
            ->distinct('payments.student_id')
            ->count('payments.student_id');

        return $this->registeredStudentsCountCache;
    }

    /**
     * Final remaining seats after subtracting registered students.
     * Returns null when `available_seats` is null.
     */
    public function getFinalAvailableSeatsAttribute(): ?int
    {
        if ($this->available_seats === null) {
            return null;
        }

        if ($this->finalAvailableSeatsCache !== null) {
            return $this->finalAvailableSeatsCache;
        }

        $registered = $this->registeredStudentsCount();
        $final = (int) $this->available_seats - $registered;

        $this->finalAvailableSeatsCache = $final < 0 ? 0 : $final;
        return $this->finalAvailableSeatsCache;
    }

    /**
     * Expose for resources: unique registered students count.
     */
    public function getRegisteredStudentsCountAttribute(): int
    {
        return $this->registeredStudentsCount();
    }

    /**
     * Get the final price considering discount
     * Returns discount_price if available, otherwise returns price
     */
    public function getFinalPrice()
    {
        if (empty($this->discount_price) || $this->discount_price == 0 || $this->discount_price === null) {
            return $this->price;
        }
        
        return $this->discount_price;
    }
}
