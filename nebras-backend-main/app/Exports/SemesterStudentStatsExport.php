<?php

namespace App\Exports;

use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;

class SemesterStudentStatsExport implements FromCollection, WithHeadings, WithEvents, WithMapping
{
    protected int $semesterId;

    protected ?Carbon $dateFrom;

    protected ?Carbon $dateTo;

    protected array $summary = [];

    public function __construct(int $semesterId, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $this->semesterId = $semesterId;
        $this->dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
        $this->dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;
    }

    public function collection(): Collection
    {
        $query = Payment::where('status', 'success')
            ->whereHas('items.course', fn ($q) => $q->where('semester_id', $this->semesterId));

        if ($this->dateFrom) {
            $query->where('paid_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('paid_at', '<=', $this->dateTo);
        }

        $studentIds = (clone $query)->distinct()->pluck('student_id')->filter()->values();
        $totalAmount = (clone $query)->sum('amount');
        $byGender = Student::whereIn('id', $studentIds)
            ->selectRaw('gender, count(*) as cnt')
            ->groupBy('gender')
            ->pluck('cnt', 'gender');
        $maleCount = (int) ($byGender->get(0, 0));
        $femaleCount = (int) ($byGender->get(1, 0));

        $this->summary = [
            'total_students' => $studentIds->count(),
            'total_amount'   => $totalAmount,
            'male_count'     => $maleCount,
            'female_count'   => $femaleCount,
        ];

        // Per-student: student_id => [total_amount, payments_count]
        $perStudent = (clone $query)
            ->selectRaw('student_id, sum(amount) as total, count(*) as cnt')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $students = Student::whereIn('id', $studentIds)->get()->keyBy('id');
        $rows = collect();

        foreach ($studentIds as $id) {
            $student = $students->get($id);
            $agg = $perStudent->get($id);
            $rows->push((object) [
                'name'          => $student?->name ?? '—',
                'email'         => $student?->email ?? '—',
                'gender'        => $student && $student->gender !== null ? ($student->gender == 0 ? __('app.male') : __('app.female')) : '—',
                'total_paid'    => $agg ? (float) $agg->total : 0,
                'payments_count'=> $agg ? (int) $agg->cnt : 0,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            __('app.student name'),
            __('app.email'),
            __('app.gender'),
            __('app.total paid amount'),
            __('app.payments count'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->email,
            $row->gender,
            '$' . (new \App\Helpers\Helper)->formatNumber($row->total_paid),
            $row->payments_count,
        ];
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $helper = new \App\Helpers\Helper;
                $summary = $this->summary;
                $sheet->insertNewRowBefore(1, 5);
                $sheet->setCellValue('A1', __('app.summary'));
                $sheet->setCellValue('A2', __('app.total students') . ' (paid):');
                $sheet->setCellValue('B2', $summary['total_students'] ?? 0);
                $sheet->setCellValue('A3', __('app.total payment amount') . ':');
                $sheet->setCellValue('B3', '$' . $helper->formatNumber($summary['total_amount'] ?? 0));
                $sheet->setCellValue('A4', __('app.male') . ':');
                $sheet->setCellValue('B4', $summary['male_count'] ?? 0);
                $sheet->setCellValue('A5', __('app.female') . ':');
                $sheet->setCellValue('B5', $summary['female_count'] ?? 0);
                $sheet->getStyle('A1:E1')->getFont()->setBold(true);
                $sheet->getStyle('A6:E6')->getFont()->setBold(true);
            },
        ];
    }
}
