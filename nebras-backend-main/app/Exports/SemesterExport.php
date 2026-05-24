<?php
namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SemesterExport implements FromQuery, WithMapping, WithHeadings, WithEvents, WithChunkReading
{
    /**
     * Query to get payments
     */
    public function query()
    {
        return Payment::where('status', 'success')
            ->whereHas('items.course.semester', function ($q) {
                $q->where('status', true);
            });
    }

    /**
     * Map each row
     */
    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->student?->name,
            $payment->student?->email,
            $payment->student?->phone,
            $payment->order_id,
            $payment->items->first()?->course?->getLocalizationTitle() ?? '-',
            "$".(new \App\Helpers\Helper)->formatNumber($payment->amount),
            optional($payment->paid_at)->format('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Define the headings
     */
    public function headings(): array
    {
        return [
            '#',
            'Student name',
            'E-mail',
            'Phone',
            'Transaction ID',
            'Course name',
            'Amount',
            'Paid At'
        ];
    }

    /**
     * Style the headings
     */
    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                $headingCount = count($this->headings());
                $columnRange = 'A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($headingCount) . '1';
                $event->sheet->getDelegate()->getStyle($columnRange)->getFont()->setBold(true);
            },
        ];
    }

    /**
     * Set chunk size
     */
    public function chunkSize(): int
    {
        return 500; // or 1000 depending on your server memory
    }
}
