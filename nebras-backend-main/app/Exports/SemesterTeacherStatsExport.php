<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Teacher;
use App\Models\Testimonial;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SemesterTeacherStatsExport implements FromCollection, WithHeadings, WithMapping
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
        $teacherIds = Course::where('semester_id', $this->semesterId)
            ->whereNotNull('teacher_id')
            ->distinct()
            ->pluck('teacher_id')
            ->filter()
            ->values();

        $teachers = Teacher::whereIn('id', $teacherIds)->get();
        $rows = collect();

        foreach ($teachers as $teacher) {
            $tid = $teacher->id;

            $itemsQuery = PaymentItem::whereHas('course', function ($q) use ($tid) {
                $q->where('teacher_id', $tid)->where('semester_id', $this->semesterId);
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

            $avgRating = Testimonial::whereHas('course', fn ($q) => $q->where('teacher_id', $tid))
                ->whereNotNull('rate')
                ->avg('rate');
            $avgRating = $avgRating !== null ? round((float) $avgRating, 2) : null;

            $rows->push((object) [
                'teacher_name'    => $teacher->getLocalizationName(),
                'students_count' => $studentsCount,
                'total_amount'   => (float) $totalAmount,
                'average_grade'  => null,
                'average_rating' => $avgRating,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            __('app.teacher name'),
            __('app.number of students'),
            __('app.total payment amount'),
            __('app.average grade'),
            __('app.rating'),
        ];
    }

    public function map($row): array
    {
        $helper = new \App\Helpers\Helper;
        return [
            $row->teacher_name,
            $row->students_count,
            '$' . $helper->formatNumber($row->total_amount),
            $row->average_grade !== null ? (string) $row->average_grade : '—',
            $row->average_rating !== null ? (string) $row->average_rating : '—',
        ];
    }
}
