<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Testimonial;
use App\Support\SemesterSubjectInsights;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();
        $semesters = Semester::orderBy('created_at', 'desc')->get();
        $activeSemester = Semester::getActive();
        $today = $this->getTodayStats($teacher->id);
        $week = $this->getWeekStats($teacher->id);
        $month = $this->getMonthStats($teacher->id);
        $year = $this->getYearStats($teacher->id);

        return view('teacher.semester-dashboard', [
            'semesters' => $semesters,
            'activeSemester' => $activeSemester,
            'today' => $today,
            'week' => $week,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function semesterDashboardStats(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);

        $semesterId = (int) $request->semester_id;
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;

        $itemsQuery = $this->baseRevenueQuery($teacher->id, $semesterId);
        if ($dateFrom) {
            $itemsQuery->whereHas('payment', fn ($q) => $q->where('paid_at', '>=', $dateFrom));
        }
        if ($dateTo) {
            $itemsQuery->whereHas('payment', fn ($q) => $q->where('paid_at', '<=', $dateTo));
        }

        $totalAmount = (clone $itemsQuery)->sum('total');
        $paymentIds = (clone $itemsQuery)->distinct()->pluck('payment_id');
        $paymentsCount = $paymentIds->count();
        $studentIds = Payment::whereIn('id', $paymentIds)->distinct()->pluck('student_id')->filter()->values();
        $totalStudentsPaid = $studentIds->count();
        $byGender = Student::whereIn('id', $studentIds)
            ->selectRaw('gender, count(*) as cnt')
            ->groupBy('gender')
            ->pluck('cnt', 'gender');

        $coursesInSemester = Course::where('semester_id', $semesterId)
            ->where('teacher_id', $teacher->id)
            ->count();
        $academicLevelsInSemester = Course::where('semester_id', $semesterId)
            ->where('teacher_id', $teacher->id)
            ->whereNotNull('academic_level_id')
            ->distinct()
            ->count('academic_level_id');
        $helper = new \App\Helpers\Helper;
        $bestClass = $this->getBestClassInRevenue($teacher->id, $semesterId, $dateFrom, $dateTo);

        return response()->json([
            'status' => true,
            'statistics' => [
                'total_amount' => $totalAmount,
                'formatted_total' => $helper->formatNumber($totalAmount),
                'payments_count' => $paymentsCount,
                'total_students_paid' => $totalStudentsPaid,
                'male_count' => (int) ($byGender->get(0, 0)),
                'female_count' => (int) ($byGender->get(1, 0)),
            ],
            'kpi' => [
                'courses_in_semester' => $coursesInSemester,
                'academic_levels_in_semester' => $academicLevelsInSemester,
            ],
            'best_class' => $bestClass,
        ]);
    }

    public function semesterDashboardTeacherStats(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);
        $semesterId = (int) $request->semester_id;
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;
        $helper = new \App\Helpers\Helper;

        $itemsQuery = PaymentItem::whereHas('course', function ($q) use ($teacher, $semesterId) {
            $q->where('teacher_id', $teacher->id)->where('semester_id', $semesterId);
        })->whereHas('payment', function ($q) use ($dateFrom, $dateTo) {
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
        $avgRating = Testimonial::whereHas('course', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->whereNotNull('rate')
            ->avg('rate');

        return response()->json([
            'status' => true,
            'teachers' => [[
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->getLocalizationName(),
                'students_count' => $studentsCount,
                'total_amount' => $totalAmount,
                'formatted_amount' => $helper->formatNumber($totalAmount),
                'average_grade' => null,
                'average_rating' => $avgRating !== null ? round((float) $avgRating, 2) : null,
            ]],
        ]);
    }

    public function semesterDashboardAcademicLevelStats(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);
        $semesterId = (int) $request->semester_id;
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;

        $levelIds = Course::where('semester_id', $semesterId)
            ->where('teacher_id', $teacher->id)
            ->whereNotNull('academic_level_id')
            ->distinct()
            ->pluck('academic_level_id')
            ->filter()
            ->values();

        $levels = AcademicLevel::whereIn('id', $levelIds)->get();
        $helper = new \App\Helpers\Helper;
        $result = [];
        foreach ($levels as $level) {
            $itemsQuery = PaymentItem::whereHas('course', function ($q) use ($level, $semesterId, $teacher) {
                $q->where('academic_level_id', $level->id)
                    ->where('semester_id', $semesterId)
                    ->where('teacher_id', $teacher->id);
            })->whereHas('payment', function ($q) use ($dateFrom, $dateTo) {
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
            $result[] = [
                'academic_level_id' => $level->id,
                'academic_level_name' => $level->getLocalizationTitle(),
                'students_count' => $studentsCount,
                'total_amount' => $totalAmount,
                'formatted_amount' => $helper->formatNumber($totalAmount),
            ];
        }

        return response()->json([
            'status' => true,
            'academic_levels' => $result,
        ]);
    }

    public function semesterDashboardSubjectStats(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);
        $semesterId = (int) $request->semester_id;
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;

        $rows = SemesterSubjectInsights::rows($semesterId, $dateFrom, $dateTo)
            ->filter(fn ($row) => (int) ($row->teacher_id ?? 0) === (int) $teacher->id)
            ->values();

        return response()->json([
            'status' => true,
            'subjects' => $rows->map(fn ($row) => [
                'course_id' => $row->course_id,
                'subject_name' => $row->subject_name,
                'grade' => $row->grade,
                'students_count' => $row->students_count,
                'total_amount' => $row->total_amount,
                'formatted_amount' => $row->formatted_amount,
                'teacher_name' => $row->teacher_name,
                'number_of_classes' => $row->number_of_classes,
            ])->all(),
        ]);
    }

    private function getTodayStats(int $teacherId): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayTotal = $this->baseRevenueQuery($teacherId)
            ->whereHas('payment', fn ($q) => $q->whereBetween('paid_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()]))
            ->sum('total');

        $yesterdayTotal = $this->baseRevenueQuery($teacherId)
            ->whereHas('payment', fn ($q) => $q->whereBetween('paid_at', [$yesterday->copy()->startOfDay(), $yesterday->copy()->endOfDay()]))
            ->sum('total');

        $percentageChange = $yesterdayTotal > 0
            ? (($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100
            : ($todayTotal > 0 ? 100 : 0);

        return [
            'today_date' => $today,
            'yesterday_date' => $yesterday,
            'today' => $todayTotal,
            'yesterday' => $yesterdayTotal,
            'percentage_change' => round($percentageChange, 2),
        ];
    }

    private function getWeekStats(int $teacherId): array
    {
        $currentWeekStart = Carbon::now()->startOfWeek();
        $currentWeekEnd = Carbon::now()->endOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        $currentTotal = $this->baseRevenueQuery($teacherId)
            ->whereHas('payment', fn ($q) => $q->whereBetween('paid_at', [$currentWeekStart, $currentWeekEnd]))
            ->sum('total');

        $previousTotal = $this->baseRevenueQuery($teacherId)
            ->whereHas('payment', fn ($q) => $q->whereBetween('paid_at', [$lastWeekStart, $lastWeekEnd]))
            ->sum('total');

        $percentageChange = $previousTotal > 0
            ? (($currentTotal - $previousTotal) / $previousTotal) * 100
            : ($currentTotal > 0 ? 100 : 0);

        return [
            'current_week_start' => $currentWeekStart,
            'current_week_end' => $currentWeekEnd,
            'last_week_start' => $lastWeekStart,
            'last_week_end' => $lastWeekEnd,
            'current_total' => $currentTotal,
            'previous_total' => $previousTotal,
            'percentage_change' => round($percentageChange, 2),
        ];
    }

    private function getMonthStats(int $teacherId): array
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $currentTotal = $this->baseRevenueQuery($teacherId)
            ->whereHas('payment', fn ($q) => $q->whereBetween('paid_at', [$currentMonthStart, $currentMonthEnd]))
            ->sum('total');

        $previousTotal = $this->baseRevenueQuery($teacherId)
            ->whereHas('payment', fn ($q) => $q->whereBetween('paid_at', [$lastMonthStart, $lastMonthEnd]))
            ->sum('total');

        $percentageChange = $previousTotal > 0
            ? (($currentTotal - $previousTotal) / $previousTotal) * 100
            : ($currentTotal > 0 ? 100 : 0);

        return [
            'current_month_start' => $currentMonthStart,
            'current_month_end' => $currentMonthEnd,
            'last_month_start' => $lastMonthStart,
            'last_month_end' => $lastMonthEnd,
            'current_total' => $currentTotal,
            'previous_total' => $previousTotal,
            'percentage_change' => round($percentageChange, 2),
        ];
    }

    private function getYearStats(int $teacherId): array
    {
        $currentYearStart = Carbon::now()->startOfYear();
        $currentYearEnd = Carbon::now()->endOfYear();
        $lastYearStart = Carbon::now()->subYear()->startOfYear();
        $lastYearEnd = Carbon::now()->subYear()->endOfYear();

        $currentTotal = $this->baseRevenueQuery($teacherId)
            ->whereHas('payment', fn ($q) => $q->whereBetween('paid_at', [$currentYearStart, $currentYearEnd]))
            ->sum('total');

        $previousTotal = $this->baseRevenueQuery($teacherId)
            ->whereHas('payment', fn ($q) => $q->whereBetween('paid_at', [$lastYearStart, $lastYearEnd]))
            ->sum('total');

        $percentageChange = $previousTotal > 0
            ? (($currentTotal - $previousTotal) / $previousTotal) * 100
            : ($currentTotal > 0 ? 100 : 0);

        return [
            'current_year_start' => $currentYearStart,
            'current_year_end' => $currentYearEnd,
            'last_year_start' => $lastYearStart,
            'last_year_end' => $lastYearEnd,
            'current_total' => $currentTotal,
            'previous_total' => $previousTotal,
            'percentage_change' => round($percentageChange, 2),
        ];
    }

    private function baseRevenueQuery(int $teacherId, ?int $semesterId = null)
    {
        return PaymentItem::query()
            ->whereHas('course', function ($q) use ($teacherId, $semesterId) {
                $q->where('teacher_id', $teacherId);
                if ($semesterId) {
                    $q->where('semester_id', $semesterId);
                }
            })
            ->whereHas('payment', fn ($q) => $q->where('status', 'success'));
    }

    private function getBestClassInRevenue(int $teacherId, int $semesterId, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $itemsQuery = $this->baseRevenueQuery($teacherId, $semesterId);
        if ($dateFrom) {
            $itemsQuery->whereHas('payment', fn ($q) => $q->where('paid_at', '>=', $dateFrom));
        }
        if ($dateTo) {
            $itemsQuery->whereHas('payment', fn ($q) => $q->where('paid_at', '<=', $dateTo));
        }

        $best = (clone $itemsQuery)
            ->with('course.classRoom')
            ->get()
            ->filter(fn ($item) => !is_null(optional($item->course)->class_id))
            ->groupBy(fn ($item) => $item->course->class_id)
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'name' => optional(optional($first->course)->classRoom)->getLocalizationName() ?? '—',
                    'total_amount' => (float) $group->sum('total'),
                ];
            })
            ->sortByDesc('total_amount')
            ->first();

        $helper = new \App\Helpers\Helper;
        return [
            'name' => $best['name'] ?? '—',
            'total_amount' => $best['total_amount'] ?? 0,
            'formatted_amount' => $helper->formatNumber($best['total_amount'] ?? 0),
        ];
    }
}
