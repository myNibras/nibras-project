<?php

namespace App\Exports;

use App\Models\AcademicLevel;
use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SemesterAcademicLevelStatsExport implements FromCollection, WithHeadings, WithMapping
{
    protected int $semesterId;

    protected ?Carbon $dateFrom;

    protected ?Carbon $dateTo;

    public function __construct(int $semesterId, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $this->semesterId = $semesterId;
        $this->dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
        $this->dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;
    }

    public function collection(): Collection
    {
        $levelIds = Course::where('semester_id', $this->semesterId)
            ->whereNotNull('academic_level_id')
            ->distinct()
            ->pluck('academic_level_id')
            ->filter()
            ->values();

        $levels = AcademicLevel::whereIn('id', $levelIds)->get();
        $rows = collect();

        foreach ($levels as $level) {
            $lid = $level->id;

            $itemsQuery = PaymentItem::whereHas('course', function ($q) use ($lid) {
                $q->where('academic_level_id', $lid)->where('semester_id', $this->semesterId);
            })->whereHas('payment', function ($q) {
                $q->where('status', 'success');
                if ($this->dateFrom) {
                    $q->where('paid_at', '>=', $this->dateFrom);
                }
                if ($this->dateTo) {
                    $q->where('paid_at', '<=', $this->dateTo);
                }
            });

            $totalAmount = (clone $itemsQuery)->sum('total');
            $paymentIds = (clone $itemsQuery)->distinct()->pluck('payment_id');
            $studentsCount = Payment::whereIn('id', $paymentIds)->distinct()->count('student_id');

            $rows->push((object) [
                'academic_level_name' => $level->getLocalizationTitle(),
                'students_count'      => $studentsCount,
                'total_amount'        => (float) $totalAmount,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            __('app.academic_level'),
            __('app.number of students'),
            __('app.total payment amount'),
        ];
    }

    public function map($row): array
    {
        $helper = new \App\Helpers\Helper;
        return [
            $row->academic_level_name,
            $row->students_count,
            '$' . $helper->formatNumber($row->total_amount),
        ];
    }
}
