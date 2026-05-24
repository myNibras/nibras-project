<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\Course;
use App\Models\AcademicLevel;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\Testimonial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\PaymentsExport;
use App\Exports\SemesterExport;
use App\Exports\SemesterStudentStatsExport;
use App\Exports\SemesterTeacherStatsExport;
use App\Exports\SemesterAcademicLevelStatsExport;
use App\Exports\SemesterSubjectStatsExport;
use App\Support\SemesterSubjectInsights;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends Controller
{
    public function index()
    {
        return $this->semesterDashboardView();
    }

    /**
     * Legacy URL: redirect to the main dashboard (same content).
     */
    public function semesterDashboard()
    {
        return redirect()->route('dashboard');
    }

    private function semesterDashboardView()
    {
        $semesters = Semester::orderBy('created_at', 'desc')->get();
        $activeSemester = Semester::getActive();

        $today = $this->getTodayStats();
        $week = $this->getWeekStats();
        $month = $this->getMonthStats();
        $year = $this->getYearStats();

        return view('semester-dashboard', compact('semesters', 'activeSemester', 'today', 'week', 'month', 'year'));
    }

    /**
     * Statistics for semester dashboard (filtered by semester + optional date range).
     * Applies only to statistics; KPI cards use their own data.
     */
    public function semesterDashboardStats(Request $request)
    {
        $rules = [
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ];
        $request->validate($rules);

        $semesterId = (int) $request->semester_id;
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;

        $query = Payment::where('status', 'success')
            ->whereHas('items.course', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });

        if ($dateFrom) {
            $query->where('paid_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('paid_at', '<=', $dateTo);
        }

        $totalAmount = (clone $query)->sum('amount');
        $paymentsCount = (clone $query)->count();

        // Student statistics (paid only): distinct students and by gender (0 = male, 1 = female)
        $studentIds = (clone $query)->distinct()->pluck('student_id')->filter()->values();
        $totalStudentsPaid = $studentIds->count();
        $byGender = Student::whereIn('id', $studentIds)
            ->selectRaw('gender, count(*) as cnt')
            ->groupBy('gender')
            ->pluck('cnt', 'gender');
        $maleCount = (int) ($byGender->get(0, 0));
        $femaleCount = (int) ($byGender->get(1, 0));

        // KPI: courses and academic levels count in semester (not affected by date range)
        $coursesInSemester = Course::where('semester_id', $semesterId)->count();
        $academicLevelsInSemester = Course::where('semester_id', $semesterId)
            ->whereNotNull('academic_level_id')
            ->distinct()
            ->count('academic_level_id');

        $helper = new \App\Helpers\Helper;

        return response()->json([
            'status' => true,
            'statistics' => [
                'total_amount'       => $totalAmount,
                'formatted_total'    => $helper->formatNumber($totalAmount),
                'payments_count'     => $paymentsCount,
                'total_students_paid'=> $totalStudentsPaid,
                'male_count'         => $maleCount,
                'female_count'       => $femaleCount,
            ],
            'kpi' => [
                'courses_in_semester'      => $coursesInSemester,
                'academic_levels_in_semester' => $academicLevelsInSemester,
            ],
        ]);
    }

    /**
     * Teacher statistics for semester dashboard (filtered by semester + optional date range).
     * Per teacher: name, students count, total payment amount, average rating (from testimonials).
     */
    public function semesterDashboardTeacherStats(Request $request)
    {
        $rules = [
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ];
        $request->validate($rules);

        $semesterId = (int) $request->semester_id;
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;

        $helper = new \App\Helpers\Helper;

        // Teachers who have at least one course in this semester
        $teacherIds = Course::where('semester_id', $semesterId)
            ->whereNotNull('teacher_id')
            ->distinct()
            ->pluck('teacher_id')
            ->filter()
            ->values();

        $teachers = Teacher::whereIn('id', $teacherIds)->get();
        $result = [];

        foreach ($teachers as $teacher) {
            $tid = $teacher->id;

            // Payment items for this teacher's courses in this semester (successful payments, optional date range)
            $itemsQuery = PaymentItem::whereHas('course', function ($q) use ($tid, $semesterId) {
                $q->where('teacher_id', $tid)->where('semester_id', $semesterId);
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

            // Distinct students: payments that have at least one item from this teacher's courses
            $paymentIds = (clone $itemsQuery)->distinct()->pluck('payment_id');
            $studentsCount = Payment::whereIn('id', $paymentIds)->distinct()->count('student_id');

            // Average rating from testimonials for this teacher's courses (optional)
            $avgRating = Testimonial::whereHas('course', fn ($q) => $q->where('teacher_id', $tid))
                ->whereNotNull('rate')
                ->avg('rate');
            $avgRating = $avgRating !== null ? round((float) $avgRating, 2) : null;

            $result[] = [
                'teacher_id'       => $teacher->id,
                'teacher_name'     => $teacher->getLocalizationName(),
                'students_count'   => $studentsCount,
                'total_amount'     => $totalAmount,
                'formatted_amount' => $helper->formatNumber($totalAmount),
                'average_grade'    => null, // optional, not in DB
                'average_rating'   => $avgRating,
            ];
        }

        return response()->json([
            'status' => true,
            'teachers' => $result,
        ]);
    }

    /**
     * Academic level statistics for semester dashboard (filtered by semester + optional date range).
     * Per level: name, students count, total payment amount.
     */
    public function semesterDashboardAcademicLevelStats(Request $request)
    {
        $rules = [
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ];
        $request->validate($rules);

        $semesterId = (int) $request->semester_id;
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;

        $helper = new \App\Helpers\Helper;

        $levelIds = Course::where('semester_id', $semesterId)
            ->whereNotNull('academic_level_id')
            ->distinct()
            ->pluck('academic_level_id')
            ->filter()
            ->values();

        $levels = AcademicLevel::whereIn('id', $levelIds)->get();
        $result = [];

        foreach ($levels as $level) {
            $lid = $level->id;

            $itemsQuery = PaymentItem::whereHas('course', function ($q) use ($lid, $semesterId) {
                $q->where('academic_level_id', $lid)->where('semester_id', $semesterId);
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
                'academic_level_id'   => $level->id,
                'academic_level_name' => $level->getLocalizationTitle(),
                'students_count'      => $studentsCount,
                'total_amount'        => $totalAmount,
                'formatted_amount'    => $helper->formatNumber($totalAmount),
            ];
        }

        return response()->json([
            'status' => true,
            'academic_levels' => $result,
        ]);
    }

    /**
     * Subject-level statistics for semester dashboard (filtered by semester + optional date range).
     * Rows are grouped by the same teacher_id and class_id (grade): aggregated payments and distinct students
     * across all courses in that group; subject names are combined; number_of_classes is the course count in the group.
     */
    public function semesterDashboardSubjectStats(Request $request)
    {
        $rules = [
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ];
        $request->validate($rules);

        $semesterId = (int) $request->semester_id;
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;

        $rows = SemesterSubjectInsights::rows($semesterId, $dateFrom, $dateTo);

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

    public function stats(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->endOfDay();

        $total = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('amount');

        return response()->json([
            'status' => true,
            'custom' => [
                'total' => (new \App\Helpers\Helper)->formatNumber($total),
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $endDate->toDateTimeString(),
            ]
        ]);
    }

    private function getTodayStats(){
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Today range
        $todayStart = $today->copy()->startOfDay();
        $todayEnd   = $today->copy()->endOfDay();

        // Yesterday range
        $yesterdayStart = $yesterday->copy()->startOfDay();
        $yesterdayEnd   = $yesterday->copy()->endOfDay();

        // Today total
        $todayTotal = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$todayStart, $todayEnd])
            ->sum('amount');

        // Yesterday total
        $yesterdayTotal = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$yesterdayStart, $yesterdayEnd])
            ->sum('amount');

        // Calculate percentage change
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

    private function getWeekStats()
    {
        $currentWeekStart = Carbon::now()->startOfWeek(); // Monday
        $currentWeekEnd   = Carbon::now()->endOfWeek();   // Sunday

        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd   = Carbon::now()->subWeek()->endOfWeek();

        $currentTotal = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$currentWeekStart, $currentWeekEnd])
            ->sum('amount');

        $previousTotal = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$lastWeekStart, $lastWeekEnd])
            ->sum('amount');

        $percentageChange = $previousTotal > 0
            ? (($currentTotal - $previousTotal) / $previousTotal) * 100
            : ($currentTotal > 0 ? 100 : 0);

        return [
            'current_week_start' => $currentWeekStart,
            'current_week_end'   => $currentWeekEnd,
            'last_week_start'    => $lastWeekStart,
            'last_week_end'      => $lastWeekEnd,
            'current_total'      => $currentTotal,
            'previous_total'     => $previousTotal,
            'percentage_change'  => round($percentageChange, 2),
        ];
    }

    private function getMonthStats()
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd   = Carbon::now()->endOfMonth();

        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonth()->endOfMonth();

        $currentTotal = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');

        $previousTotal = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        $percentageChange = $previousTotal > 0
            ? (($currentTotal - $previousTotal) / $previousTotal) * 100
            : ($currentTotal > 0 ? 100 : 0);

        return [
            'current_month_start' => $currentMonthStart,
            'current_month_end'   => $currentMonthEnd,
            'last_month_start'    => $lastMonthStart,
            'last_month_end'      => $lastMonthEnd,
            'current_total'       => $currentTotal,
            'previous_total'      => $previousTotal,
            'percentage_change'   => round($percentageChange, 2),
        ];
    }

    private function getYearStats()
    {
        $currentYearStart = Carbon::now()->startOfYear();
        $currentYearEnd   = Carbon::now()->endOfYear();

        $lastYearStart = Carbon::now()->subYear()->startOfYear();
        $lastYearEnd   = Carbon::now()->subYear()->endOfYear();

        $currentTotal = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$currentYearStart, $currentYearEnd])
            ->sum('amount');

        $previousTotal = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$lastYearStart, $lastYearEnd])
            ->sum('amount');

        $percentageChange = $previousTotal > 0
            ? (($currentTotal - $previousTotal) / $previousTotal) * 100
            : ($currentTotal > 0 ? 100 : 0);

        return [
            'current_year_start' => $currentYearStart,
            'current_year_end'   => $currentYearEnd,
            'last_year_start'    => $lastYearStart,
            'last_year_end'      => $lastYearEnd,
            'current_total'      => $currentTotal,
            'previous_total'     => $previousTotal,
            'percentage_change'  => round($percentageChange, 2),
        ];
    }

    public function export(Request $request)
    {
        $start = Carbon::parse($request->start)->startOfDay();
        $end = Carbon::parse($request->end)->endOfDay();
        $type = $request->service_type ?? 'na';
        switch($type){
            case 'semester-payments':
                return Excel::download(new SemesterExport(), 'semester.xlsx');
            break;
            default:
                return Excel::download(new PaymentsExport($start, $end), 'payments.xlsx');
            break;
        }
    }

    /**
     * Export semester student statistics (CSV or Excel) according to current filter.
     */
    public function exportSemesterStudentStats(Request $request): BinaryFileResponse
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'date_from'    => 'nullable|date',
            'date_to'      => 'nullable|date|after_or_equal:date_from',
            'format'       => 'required|in:csv,xlsx',
        ]);

        $export = new SemesterStudentStatsExport(
            (int) $request->semester_id,
            $request->date_from,
            $request->date_to
        );

        $filename = 'semester-student-stats-' . now()->format('Y-m-d-His');

        if ($request->format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename . '.xlsx');
    }

    /**
     * Export semester teacher statistics (CSV or Excel) according to current filter.
     */
    public function exportSemesterTeacherStats(Request $request): BinaryFileResponse
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'format'      => 'required|in:csv,xlsx',
        ]);

        $export = new SemesterTeacherStatsExport(
            (int) $request->semester_id,
            $request->date_from,
            $request->date_to
        );

        $filename = 'semester-teacher-stats-' . now()->format('Y-m-d-His');

        if ($request->format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename . '.xlsx');
    }

    /**
     * Export semester academic level statistics (CSV or Excel) according to current filter.
     */
    public function exportSemesterAcademicLevelStats(Request $request): BinaryFileResponse
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'format'      => 'required|in:csv,xlsx',
        ]);

        $export = new SemesterAcademicLevelStatsExport(
            (int) $request->semester_id,
            $request->date_from,
            $request->date_to
        );

        $filename = 'semester-academic-level-stats-' . now()->format('Y-m-d-His');

        if ($request->format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename . '.xlsx');
    }

    /**
     * Export semester subject (course) statistics (CSV or Excel) according to current filter.
     */
    public function exportSemesterSubjectStats(Request $request): BinaryFileResponse
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'format'      => 'required|in:csv,xlsx',
        ]);

        $export = new SemesterSubjectStatsExport(
            (int) $request->semester_id,
            $request->date_from,
            $request->date_to
        );

        $filename = 'semester-subject-stats-' . now()->format('Y-m-d-His');

        if ($request->format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename . '.xlsx');
    }
}
