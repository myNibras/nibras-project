<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;

    public function __construct($student)
    {
        $this->student = $student;
    }

    public function build()
    {
        $subject = (app()->getLocale() == "ar") ? "🎉 أهلاً بك في منصة نبراس التعليمية!" : "🎉 Welcome to Nibbraas Learning Platform!";
        $template = (app()->getLocale() == "ar") ? "emails.registration" : "emails.registration_en";
        return $this->subject($subject)
                    ->view($template);
    }
}
