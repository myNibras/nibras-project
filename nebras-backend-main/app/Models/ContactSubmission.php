<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'country',
        'subject',
        'message',
    ];

    /**
     * Display label for subject: translate known i18n keys, otherwise raw stored value.
     */
    public static function localizedSubject(?string $subject): string
    {
        if ($subject === null || $subject === '') {
            return '—';
        }

        return match ($subject) {
            'contact.subject.support' => __('app.contact.subject.support'),
            'contact.subject.inquiry' => __('app.contact.subject.inquiry'),
            'contact.subject.suggestion' => __('app.contact.subject.suggestion'),
            default => $subject,
        };
    }
}
