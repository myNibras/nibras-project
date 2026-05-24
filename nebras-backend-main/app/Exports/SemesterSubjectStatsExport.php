<?php

namespace App\Exports;

use App\Support\SemesterSubjectInsights;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SemesterSubjectStatsExport implements FromCollection, WithHeadings, WithMapping
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
        return SemesterSubjectInsights::rows($this->semesterId, $this->dateFrom, $this->dateTo);
    }

    public function headings(): array
    {
        return [
            __('app.subject name'),
            __('app.grade'),
            __('app.number of students'),
            __('app.total payment amount'),
            __('app.teacher name'),
            __('app.number of classes'),
        ];
    }

    public function map($row): array
    {
        $helper = new \App\Helpers\Helper;
        return [
            $row->subject_name,
            $row->grade,
            $row->students_count,
            '$' . $helper->formatNumber($row->total_amount),
            $row->teacher_name,
            $row->number_of_classes,
        ];
    }
}
