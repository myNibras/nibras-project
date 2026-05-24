<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $student;

    public function __construct($otp, $student)
    {
        $this->otp = $otp;
        $this->student = $student;
    }

    public function build()
    {
        $subject = (app()->getLocale() == "ar") ? "رمز التحقق الخاص بك (OTP)" : "Your Verification Code (OTP)";
        $template = (app()->getLocale() == "ar") ? "emails.otp" : "emails.otp_en";
        return $this->subject($subject)
                    ->view($template);
    }
}
