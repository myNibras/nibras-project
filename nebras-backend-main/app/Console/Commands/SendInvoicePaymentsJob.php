<?php

namespace App\Console\Commands;

use App\Models\Installment;
use App\Models\Payment;
use App\Mail\InvoiceMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;

class SendInvoicePaymentsJob extends Command
{
    protected $signature = 'app:send-invoice-payments-job';
    protected $description = 'Send payment invoice to email';

    public function handle()
    {
        $payments = Payment::with(['items.installments', 'items.course.classRoom', 'items.course.teacher', 'coupon', 'student'])
            ->where('status', 'success')
            ->where('send_invoice', 0)
            ->take(10)
            ->get();

        foreach ($payments as $payment) {
            try {
                $data = [
                    'payment' => $payment,
                    'student' => $payment->student,
                ];

                if (!empty($payment->installment_ids)) {
                    $data['installmentsForInvoice'] = Installment::whereIn('id', $payment->installment_ids)
                        ->with('paymentItem.course.classRoom', 'paymentItem.course.teacher')
                        ->get();
                }

                $pdf_template = $payment->language === "ar" ? 'pdfs.invoice' : 'pdfs.invoice_en';

                $pdf = LaravelMpdf::loadView($pdf_template, $data, [], [
                    'format'                     => 'A4'
                ]);

                $pdfFileName = "invoice-{$payment->id}.pdf";
                $invoicePath = storage_path('app/invoices/'.$pdfFileName);
                $pdf->save($invoicePath);

                // Clear previous media
                $payment->clearMediaCollection('invoices');

                // Save PDF using Spatie Media Library
                $payment->addMedia($invoicePath)->usingFileName($pdfFileName)->toMediaCollection('invoices');

                // Send email with PDF attached
                Mail::to($payment->student->email)->cc('bills@mynibras.com')->send(new InvoiceMail($payment, $payment->student));

                // // Mark as sent
                $payment->update(['send_invoice' => true]);

                $this->info("Invoice sent for payment ID: {$payment->id}");

            } catch (\Throwable $e) {
                Log::error('Failed to send invoice email', [
                    'payment_id' => $payment->id,
                    'student_email' => $payment->student->email,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Failed to send invoice for payment ID: {$payment->id}");
            }
        }
    }
}
