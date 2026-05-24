<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $student;

    /**
     * Create a new message instance.
     */
    public function __construct($payment, $student)
    {
        $this->payment = $payment;
        $this->student = $student;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: ($this->payment->language == "ar") ? 'نبراس – إيصال الدفع' : 'Nibras – Your Payment Receipt',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: ($this->payment->language == "ar") ? 'emails.invoice' : 'emails.invoice_en',
        );
    }

    /**
     * Attach files if needed.
     */
    public function attachments(): array
    {
        if ($this->payment->getFirstMedia('invoices')) {
            return [
                Attachment::fromPath($this->payment->getFirstMedia('invoices')->getPath())->as(($this->payment->language == "ar") ? 'إيصال دفع نبراس.pdf' : 'Nibras Payment Receipt.pdf')->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
