<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Student extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'age',
        'gender', //e.g., 0 = male, 1 = female
        'phone',
        'country',
        'class_id',
        'otp_code',
        'otp_expires_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'media',
    ];

    protected $appends = ['profile_picture'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the class that the student belongs to.
     */
    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Get the payments for the student.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * In-app notification messages (not Laravel database notifications).
     */
    public function notificationMessages()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the profile picture attribute.
     * If student has no profile picture, return the image from academic level via student's class.
     */
    public function getProfilePictureAttribute()
    {
        // First, check if student has a profile picture
        $photo = $this->getMedia('profile_pictures')->first();
        if ($photo) {
            return $photo->getUrl();
        }

        // If no profile picture, get image from academic level via student's class
        if ($this->class_id) {
            // Load class relationship if not already loaded
            if (!$this->relationLoaded('classRoom')) {
                $this->load('classRoom');
            }
            
            if ($this->classRoom && $this->classRoom->academic_level_id) {
                // Load academic level relationship if not already loaded
                if (!$this->classRoom->relationLoaded('academicLevel')) {
                    $this->classRoom->load('academicLevel');
                }
                
                if ($this->classRoom->academicLevel) {
                    $academicLevelPhoto = $this->classRoom->academicLevel->getMedia('academic_level')->first();
                    if ($academicLevelPhoto) {
                        return $academicLevelPhoto->getUrl();
                    }
                }
            }
        }

        return null;
    }
}
