<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SemesterSubjectInsights
{
    /**
     * Subject insight rows for a semester: one row per distinct (teacher_id, class_id),
     * with payment stats aggregated across all courses sharing that pair.
     */
    public static function rows(int $semesterId, ?Carbon $dateFrom = null, ?Carbon $dateTo = null): Collection
    {
        $helper = new \App\Helpers\Helper;

        $courses = Course::with(['classRoom', 'teacher'])
            ->where('semester_id', $semesterId)
            ->get();

        $grouped = $courses->groupBy(function (Course $course) {
            $tid = $course->teacher_id ?? '_null_';
            $cid = $course->class_id ?? '_null_';

            return $tid."\0".$cid;
        });

        $rows = collect();

        foreach ($grouped as $group) {
            $courseIds = $group->pluck('id')->all();

            $itemsQuery = PaymentItem::whereIn('course_id', $courseIds)
                ->whereHas('payment', function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'success');
                    if ($dateFrom) {
                        $q->where('paid_at', '>=', $dateFrom);
                    }
                    if ($dateTo) {
                        $q->where('paid_at', '<=', $dateTo);
                    }
                });

            $totalAmount = (clone $itemsQuery)->sum('total');
            $paymentIds = (clone $itemsQuery)->distinct()->pluck('payment_id');
            $studentsCount = Payment::whereIn('id', $paymentIds)->distinct()->count('student_id');

            $first = $group->first();
            $titles = $group->map(fn (Course $c) => $c->getLocalizationTitle())->unique()->values();
            $subjectName = $titles->isNotEmpty()
                ? $titles->implode(' · ')
                : '—';

            $rows->push((object) [
                'course_id' => $first->id,
                'subject_name' => $subjectName,
                'grade' => $first->classRoom ? $first->classRoom->getLocalizationGradeLabel() : '—',
                'students_count' => $studentsCount,
                'total_amount' => (float) $totalAmount,
                'formatted_amount' => $helper->formatNumber($totalAmount),
                'teacher_name' => $first->teacher ? $first->teacher->getLocalizationName() : '—',
                'number_of_classes' => $group->count(),
            ]);
        }

        return $rows->sortBy(
            fn ($r) => mb_strtolower($r->teacher_name.'|'.$r->grade.'|'.$r->subject_name)
        )->values();
    }
}
