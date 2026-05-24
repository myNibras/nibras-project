@extends('layouts.app')
@section('title'){{ __('app.dashboard') }}@endsection

@section('content')
<div class="container-fluid py-4 px-3">
    {{-- Page header --}}
    <div class="row mb-4">
        <div class="col">
            <h3 class="mb-1 h4 font-weight-bolder">{{ __('app.dashboard') }}</h3>
            <p class="text-sm text-muted mb-0">{{ __('app.semester dashboard description') }}</p>
        </div>
    </div>

    {{-- Financial KPIs: first row, 4 equal cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-xs text-secondary mb-1">{{ __('app.today') }}</p>
                            <h4 class="mb-0 font-weight-bolder">${{ (new \App\Helpers\Helper)->formatNumber($today['today']) }}</h4>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-dark p-1 btn-download-excel" data-type="payments" data-start="{{ $today['today_date'] }}" data-end="{{ $today['today_date'] }}" title="{{ __('app.export') }}">
                            <i class="material-symbols-rounded" style="font-size: 1.25rem;">download</i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-2 px-3 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-xs text-muted">{{ __('app.yesterday') }}: ${{ (new \App\Helpers\Helper)->formatNumber($today['yesterday']) }}</span>
                        @if($today['percentage_change'] > 0)
                            <span class="badge badge-sm bg-success">+{{ $today['percentage_change'] }}%</span>
                        @else
                            <span class="badge badge-sm bg-danger">{{ $today['percentage_change'] }}%</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-xs text-secondary mb-1">{{ __('app.week') }}</p>
                            <h4 class="mb-0 font-weight-bolder">${{ (new \App\Helpers\Helper)->formatNumber($week['current_total']) }}</h4>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-dark p-1 btn-download-excel" data-type="payments" data-start="{{ $week['current_week_start'] }}" data-end="{{ $week['current_week_end'] }}" title="{{ __('app.export') }}">
                            <i class="material-symbols-rounded" style="font-size: 1.25rem;">download</i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-2 px-3 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-xs text-muted">{{ __('app.last week') }}: ${{ (new \App\Helpers\Helper)->formatNumber($week['previous_total']) }}</span>
                        @if($week['percentage_change'] > 0)
                            <span class="badge badge-sm bg-success">+{{ $week['percentage_change'] }}%</span>
                        @else
                            <span class="badge badge-sm bg-danger">{{ $week['percentage_change'] }}%</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-xs text-secondary mb-1">{{ __('app.month') }}</p>
                            <h4 class="mb-0 font-weight-bolder">${{ (new \App\Helpers\Helper)->formatNumber($month['current_total']) }}</h4>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-dark p-1 btn-download-excel" data-type="payments" data-start="{{ $month['current_month_start'] }}" data-end="{{ $month['current_month_end'] }}" title="{{ __('app.export') }}">
                            <i class="material-symbols-rounded" style="font-size: 1.25rem;">download</i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-2 px-3 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-xs text-muted">{{ __('app.last month') }}: ${{ (new \App\Helpers\Helper)->formatNumber($month['previous_total']) }}</span>
                        @if($month['percentage_change'] > 0)
                            <span class="badge badge-sm bg-success">+{{ $month['percentage_change'] }}%</span>
                        @else
                            <span class="badge badge-sm bg-danger">{{ $month['percentage_change'] }}%</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-xs text-secondary mb-1">{{ __('app.year') }}</p>
                            <h4 class="mb-0 font-weight-bolder">${{ (new \App\Helpers\Helper)->formatNumber($year['current_total']) }}</h4>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-dark p-1 btn-download-excel" data-type="payments" data-start="{{ $year['current_year_start'] }}" data-end="{{ $year['current_year_end'] }}" title="{{ __('app.export') }}">
                            <i class="material-symbols-rounded" style="font-size: 1.25rem;">download</i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-2 px-3 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-xs text-muted">{{ __('app.last year') }}: ${{ (new \App\Helpers\Helper)->formatNumber($year['previous_total']) }}</span>
                        @if($year['percentage_change'] > 0)
                            <span class="badge badge-sm bg-success">+{{ $year['percentage_change'] }}%</span>
                        @else
                            <span class="badge badge-sm bg-danger">{{ $year['percentage_change'] }}%</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3 px-4">
            <div class="row align-items-end g-3 g-md-4">
                <div class="col-12 col-sm-auto">
                    <label for="semester-select" class="form-label small text-secondary mb-1">{{ __('app.select semester') }}</label>
                    <select id="semester-select" class="form-select form-select-sm" style="min-width: 200px;">
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ $activeSemester && $activeSemester->id === $semester->id ? 'selected' : '' }}>
                                {{ $semester->getLocalizationTitle() }}{{ $semester->status ? ' (' . __('app.active') . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-auto">
                    <label for="date-from" class="form-label small text-secondary mb-1">{{ __('app.date from') }}</label>
                    <input type="text" id="date-from" class="form-control form-control-sm" placeholder="{{ __('app.optional') }}" style="min-width: 130px;" readonly>
                </div>
                <div class="col-12 col-sm-auto">
                    <label for="date-to" class="form-label small text-secondary mb-1">{{ __('app.date to') }}</label>
                    <input type="text" id="date-to" class="form-control form-control-sm" placeholder="{{ __('app.optional') }}" style="min-width: 130px;" readonly>
                    <small id="date-range-error" class="text-danger d-none">{{ __('app.date from must be before or equal to date to') }}</small>
                </div>
                <div class="col-12 col-sm-auto">
                    <label class="form-label small text-secondary mb-1 d-block">&nbsp;</label>
                    <button type="button" id="btn-reset-dates" class="btn btn-outline-secondary btn-sm">
                        {{ __('app.reset dates') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Section: Overview (KPI + Statistics in one row) --}}
    <h6 class="text-uppercase text-secondary font-weight-bolder mb-3 pb-2 border-bottom">{{ __('app.statistics') }}</h6>
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-4">
                    <div class="icon icon-shape icon-lg bg-gradient-dark shadow border-radius-lg d-inline-flex justify-content-center mb-2">
                        <i class="material-symbols-rounded opacity-10">book_ribbon</i>
                    </div>
                    <h6 class="text-secondary mb-1">{{ __('app.courses in semester') }}</h6>
                    <h4 class="mb-0 font-weight-bolder" id="kpi-courses">{{ __('app.loading') }}...</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-4">
                    <div class="icon icon-shape icon-lg bg-gradient-primary shadow border-radius-lg d-inline-flex justify-content-center mb-2">
                        <i class="material-symbols-rounded opacity-10 text-white">groups</i>
                    </div>
                    <h6 class="text-secondary mb-1">{{ __('app.students') }}</h6>
                    <h4 class="mb-0 font-weight-bolder" id="kpi-students">{{ __('app.loading') }}...</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-4">
                    <div class="icon icon-shape icon-lg bg-gradient-info shadow border-radius-lg d-inline-flex justify-content-center mb-2">
                        <i class="material-symbols-rounded opacity-10 text-white">school</i>
                    </div>
                    <h6 class="text-secondary mb-1">{{ __('app.academic levels') }}</h6>
                    <h4 class="mb-0 font-weight-bolder" id="kpi-academic-levels">{{ __('app.loading') }}...</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3 px-4">
                    <p class="text-xs text-secondary text-uppercase mb-1">{{ __('app.total revenue') }}</p>
                    <h4 class="mb-0 font-weight-bolder">$<span id="stat-total">0</span></h4>
                    <p class="text-xs text-muted mt-2 mb-0">{{ __('app.payments count') }}: <strong id="stat-count">0</strong></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Section: Student statistics --}}
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <h6 class="text-uppercase text-secondary font-weight-bolder">{{ __('app.student statistics') }}</h6>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="#" id="btn-export-csv" class="btn btn-outline-dark btn-sm">{{ __('app.export') }} CSV</a>
            <a href="#" id="btn-export-excel" class="btn btn-outline-dark btn-sm">{{ __('app.export') }} Excel</a>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-xs text-secondary text-uppercase mb-1">{{ __('app.total students') }}</p>
                            <h4 class="mb-0 font-weight-bolder" id="stat-students-paid">0</h4>
                        </div>
                        <div class="icon icon-shape d-flex justify-content-center bg-gradient-primary shadow border-radius-lg">
                            <i class="material-symbols-rounded opacity-10 text-white">groups</i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-xs text-secondary text-uppercase mb-1">{{ __('app.total payment amount') }}</p>
                            <h4 class="mb-0 font-weight-bolder">$<span id="stat-students-total">0</span></h4>
                        </div>
                        <div class="icon icon-shape d-flex justify-content-center bg-gradient-success shadow border-radius-lg">
                            <i class="material-symbols-rounded opacity-10 text-white">payments</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <h6 class="mb-2">{{ __('app.students by gender') }}</h6>
                    <div style="position: relative; height: 200px;">
                        <canvas id="chart-gender"></canvas>
                    </div>
                    <div class="d-flex justify-content-center gap-3 mt-2 text-sm">
                        <span><span class="badge bg-primary">●</span> {{ __('app.male') }}: <strong id="label-male">0</strong></span>
                        <span><span class="badge bg-info">●</span> {{ __('app.female') }}: <strong id="label-female">0</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section: Teacher statistics --}}
    <h6 class="text-uppercase text-secondary font-weight-bolder mb-3 pb-2 border-bottom mt-4">{{ __('app.teacher statistics') }}</h6>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3 px-4">
            <span class="text-sm text-muted">{{ __('app.statistics') }}</span>
            <div class="d-flex gap-2">
                <a href="#" id="btn-export-teacher-csv" class="btn btn-outline-dark btn-sm">CSV</a>
                <a href="#" id="btn-export-teacher-excel" class="btn btn-outline-dark btn-sm">Excel</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-items-center mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder ps-4 sortable" data-sort="name">{{ __('app.teacher name') }}</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder sortable" data-sort="students">{{ __('app.number of students') }}</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder sortable" data-sort="amount">{{ __('app.total payment amount') }}</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder sortable" data-sort="grade">{{ __('app.average grade') }}</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder sortable" data-sort="rating">{{ __('app.rating') }}</th>
                    </tr>
                </thead>
                <tbody id="teacher-stats-tbody">
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('app.loading') }}...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section: Academic level statistics --}}
    <h6 class="text-uppercase text-secondary font-weight-bolder mb-3 pb-2 border-bottom mt-4">{{ __('app.academic level statistics') }}</h6>
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3 px-4">
                    <span class="text-sm text-muted">{{ __('app.statistics') }}</span>
                    <div class="d-flex gap-2">
                        <a href="#" id="btn-export-level-csv" class="btn btn-outline-dark btn-sm">CSV</a>
                        <a href="#" id="btn-export-level-excel" class="btn btn-outline-dark btn-sm">Excel</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-items-center mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder ps-4">{{ __('app.academic_level') }}</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder">{{ __('app.number of students') }}</th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder">{{ __('app.total payment amount') }}</th>
                            </tr>
                        </thead>
                        <tbody id="academic-level-stats-tbody">
                            <tr><td colspan="3" class="text-center text-muted py-4">{{ __('app.loading') }}...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-0 py-3 px-4">
                    <h6 class="mb-0">{{ __('app.academic level statistics') }} — {{ __('app.total payment amount') }}</h6>
                </div>
                <div class="card-body p-3">
                    <div style="position: relative; height: 280px;">
                        <canvas id="chart-academic-level"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section: Subject insights --}}
    <h6 class="text-uppercase text-secondary font-weight-bolder mb-3 pb-2 border-bottom mt-4">{{ __('app.subject insights') }}</h6>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3 px-4">
            <span class="text-sm text-muted">{{ __('app.statistics') }}</span>
            <div class="d-flex gap-2">
                <a href="#" id="btn-export-subject-csv" class="btn btn-outline-dark btn-sm">CSV</a>
                <a href="#" id="btn-export-subject-excel" class="btn btn-outline-dark btn-sm">Excel</a>
            </div>
        </div>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover align-items-center mb-0">
                <thead class="bg-light position-sticky top-0 bg-light z-1">
                    <tr>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder">{{ __('app.grade') }}</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder">{{ __('app.number of students') }}</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder">{{ __('app.total payment amount') }}</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder">{{ __('app.teacher name') }}</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder">{{ __('app.number of classes') }}</th>
                        <th class="text-uppercase text-secondary text-xs font-weight-bolder ps-4">{{ __('app.subject name') }}</th>
                    </tr>
                </thead>
                <tbody id="subject-stats-tbody">
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('app.loading') }}...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
<script>
(function() {
    var statsUrl = "{{ route('dashboard.semester.stats') }}";
    var exportBaseUrl = "{{ route('dashboard.semester.export.student-stats') }}";
    var teacherStatsUrl = "{{ route('dashboard.semester.teacher-stats') }}";
    var teacherExportBaseUrl = "{{ route('dashboard.semester.export.teacher-stats') }}";
    var academicLevelStatsUrl = "{{ route('dashboard.semester.academic-level-stats') }}";
    var academicLevelExportBaseUrl = "{{ route('dashboard.semester.export.academic-level-stats') }}";
    var subjectStatsUrl = "{{ route('dashboard.semester.subject-stats') }}";
    var subjectExportBaseUrl = "{{ route('dashboard.semester.export.subject-stats') }}";
    var semesterSelect = document.getElementById('semester-select');
    var dateFromInput = document.getElementById('date-from');
    var dateToInput = document.getElementById('date-to');
    var dateRangeError = document.getElementById('date-range-error');
    var kpiCourses = document.getElementById('kpi-courses');
    var kpiStudents = document.getElementById('kpi-students');
    var kpiAcademicLevels = document.getElementById('kpi-academic-levels');
    var statTotal = document.getElementById('stat-total');
    var statCount = document.getElementById('stat-count');
    var statStudentsPaid = document.getElementById('stat-students-paid');
    var statStudentsTotal = document.getElementById('stat-students-total');
    var labelMale = document.getElementById('label-male');
    var labelFemale = document.getElementById('label-female');
    var options = @json($semesters->map(fn ($s) => ['id' => $s->id, 'title' => $s->getLocalizationTitle()]));

    var maleLabel = "{{ __('app.male') }}";
    var femaleLabel = "{{ __('app.female') }}";
    var chartGender = null;
    var teacherStatsData = [];
    var teacherSortCol = null;
    var teacherSortDir = 1;
    var academicLevelStatsData = [];
    var chartAcademicLevel = null;

    function isRangeValid() {
        var from = dateFromInput.value ? new Date(dateFromInput.value) : null;
        var to = dateToInput.value ? new Date(dateToInput.value) : null;
        if (!from || !to) return true;
        return from.getTime() <= to.getTime();
    }

    function showRangeError(show) {
        if (show) dateRangeError.classList.remove('d-none');
        else dateRangeError.classList.add('d-none');
    }

    function updateSelectedInfo() {
        // Semester selection updated; stats are refreshed via onFilterChange -> fetchStats
    }

    function buildExportUrl(format) {
        var params = new URLSearchParams({ semester_id: semesterSelect.value, format: format });
        if (dateFromInput.value) params.set('date_from', dateFromInput.value);
        if (dateToInput.value) params.set('date_to', dateToInput.value);
        return exportBaseUrl + '?' + params.toString();
    }

    function updateExportButtons() {
        document.getElementById('btn-export-csv').href = buildExportUrl('csv');
        document.getElementById('btn-export-excel').href = buildExportUrl('xlsx');
        var tParams = new URLSearchParams({ semester_id: semesterSelect.value, format: 'csv' });
        if (dateFromInput.value) tParams.set('date_from', dateFromInput.value);
        if (dateToInput.value) tParams.set('date_to', dateToInput.value);
        document.getElementById('btn-export-teacher-csv').href = teacherExportBaseUrl + '?' + tParams.toString();
        tParams.set('format', 'xlsx');
        document.getElementById('btn-export-teacher-excel').href = teacherExportBaseUrl + '?' + tParams.toString();
        var aParams = new URLSearchParams({ semester_id: semesterSelect.value, format: 'csv' });
        if (dateFromInput.value) aParams.set('date_from', dateFromInput.value);
        if (dateToInput.value) aParams.set('date_to', dateToInput.value);
        document.getElementById('btn-export-level-csv').href = academicLevelExportBaseUrl + '?' + aParams.toString();
        aParams.set('format', 'xlsx');
        document.getElementById('btn-export-level-excel').href = academicLevelExportBaseUrl + '?' + aParams.toString();
        var sParams = new URLSearchParams({ semester_id: semesterSelect.value, format: 'csv' });
        if (dateFromInput.value) sParams.set('date_from', dateFromInput.value);
        if (dateToInput.value) sParams.set('date_to', dateToInput.value);
        document.getElementById('btn-export-subject-csv').href = subjectExportBaseUrl + '?' + sParams.toString();
        sParams.set('format', 'xlsx');
        document.getElementById('btn-export-subject-excel').href = subjectExportBaseUrl + '?' + sParams.toString();
    }

    function renderSubjectTable(subjects) {
        var tbody = document.getElementById('subject-stats-tbody');
        if (!tbody) return;
        if (!subjects || subjects.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">—</td></tr>';
            return;
        }
        var html = subjects.map(function(s) {
            return '<tr><td>' + escapeHtml(s.grade) + '</td><td>' + s.students_count + '</td><td>$' + escapeHtml(s.formatted_amount) + '</td><td>' + escapeHtml(s.teacher_name) + '</td><td>' + s.number_of_classes + '</td><td class="ps-3">' + escapeHtml(s.subject_name) + '</td></tr>';
        }).join('');
        tbody.innerHTML = html;
    }

    function renderAcademicLevelTable(levels) {
        var tbody = document.getElementById('academic-level-stats-tbody');
        if (!tbody) return;
        if (!levels || levels.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">—</td></tr>';
            return;
        }
        var html = levels.map(function(l) {
            return '<tr><td class="ps-3">' + escapeHtml(l.academic_level_name) + '</td><td>' + l.students_count + '</td><td>$' + escapeHtml(l.formatted_amount) + '</td></tr>';
        }).join('');
        tbody.innerHTML = html;
    }

    function updateAcademicLevelChart(levels) {
        var ctx = document.getElementById('chart-academic-level');
        if (!ctx || !levels || levels.length === 0) {
            if (chartAcademicLevel) { chartAcademicLevel.destroy(); chartAcademicLevel = null; }
            return;
        }
        var labels = levels.map(function(l) { return l.academic_level_name; });
        var data = levels.map(function(l) { return l.total_amount; });
        if (chartAcademicLevel) {
            chartAcademicLevel.data.labels = labels;
            chartAcademicLevel.data.datasets[0].data = data;
            chartAcademicLevel.update();
            return;
        }
        chartAcademicLevel = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '{{ __("app.total payment amount") }}',
                    data: data,
                    backgroundColor: 'rgba(26, 115, 232, 0.8)',
                    borderColor: 'rgba(26, 115, 232, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 0,
                            minRotation: 0,
                            callback: function(value, index, ticks) {
                                var label = this.getLabelForValue(value);
                                if (typeof label !== 'string' || label.length <= 12) return label;
                                var mid = Math.ceil(label.length / 2);
                                var space = label.lastIndexOf(' ', mid);
                                if (space <= 0) space = mid;
                                return label.substring(0, space) + '\n' + label.substring(space).trim();
                            }
                        }
                    },
                    y: { beginAtZero: true }
                }
            }
        });
    }

    function renderTeacherTable(teachers) {
        var tbody = document.getElementById('teacher-stats-tbody');
        if (!tbody) return;
        if (!teachers || teachers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">—</td></tr>';
            return;
        }
        var html = teachers.map(function(t) {
            var grade = t.average_grade != null ? t.average_grade : '—';
            var rating = t.average_rating != null ? t.average_rating : '—';
            return '<tr><td class="ps-3">' + escapeHtml(t.teacher_name) + '</td><td>' + t.students_count + '</td><td>$' + escapeHtml(t.formatted_amount) + '</td><td>' + grade + '</td><td>' + rating + '</td></tr>';
        }).join('');
        tbody.innerHTML = html;
    }
    function escapeHtml(s) { var div = document.createElement('div'); div.textContent = s; return div.innerHTML; }

    function sortTeacherStats(col) {
        if (teacherSortCol === col) teacherSortDir = -teacherSortDir; else { teacherSortCol = col; teacherSortDir = 1; }
        var key = col === 'name' ? 'teacher_name' : col === 'students' ? 'students_count' : col === 'amount' ? 'total_amount' : col === 'grade' ? 'average_grade' : 'average_rating';
        teacherStatsData.sort(function(a, b) {
            var va = a[key], vb = b[key];
            if (key === 'teacher_name') return teacherSortDir * (String(va).localeCompare(String(vb)));
            if (va == null) va = key === 'average_grade' || key === 'average_rating' ? -1 : 0;
            if (vb == null) vb = key === 'average_grade' || key === 'average_rating' ? -1 : 0;
            return teacherSortDir * (va - vb);
        });
        renderTeacherTable(teacherStatsData);
    }

    function fetchTeacherStats() {
        if (!isRangeValid()) return;
        var tbody = document.getElementById('teacher-stats-tbody');
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">{{ __("app.loading") }}...</td></tr>';
        var params = { semester_id: semesterSelect.value };
        if (dateFromInput.value) params.date_from = dateFromInput.value;
        if (dateToInput.value) params.date_to = dateToInput.value;
        $.get(teacherStatsUrl, params).done(function(data) {
            if (data.status && data.teachers) {
                teacherStatsData = data.teachers;
                sortTeacherStats(teacherSortCol || 'amount');
            } else {
                teacherStatsData = [];
                renderTeacherTable([]);
            }
        }).fail(function() {
            teacherStatsData = [];
            renderTeacherTable([]);
        });
    }

    function fetchAcademicLevelStats() {
        if (!isRangeValid()) return;
        var tbody = document.getElementById('academic-level-stats-tbody');
        if (tbody) tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">{{ __("app.loading") }}...</td></tr>';
        var params = { semester_id: semesterSelect.value };
        if (dateFromInput.value) params.date_from = dateFromInput.value;
        if (dateToInput.value) params.date_to = dateToInput.value;
        $.get(academicLevelStatsUrl, params).done(function(data) {
            if (data.status && data.academic_levels) {
                academicLevelStatsData = data.academic_levels;
                renderAcademicLevelTable(academicLevelStatsData);
                updateAcademicLevelChart(academicLevelStatsData);
            } else {
                academicLevelStatsData = [];
                renderAcademicLevelTable([]);
                updateAcademicLevelChart([]);
            }
        }).fail(function() {
            academicLevelStatsData = [];
            renderAcademicLevelTable([]);
            updateAcademicLevelChart([]);
        });
    }

    function fetchSubjectStats() {
        if (!isRangeValid()) return;
        var tbody = document.getElementById('subject-stats-tbody');
        if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">{{ __("app.loading") }}...</td></tr>';
        var params = { semester_id: semesterSelect.value };
        if (dateFromInput.value) params.date_from = dateFromInput.value;
        if (dateToInput.value) params.date_to = dateToInput.value;
        $.get(subjectStatsUrl, params).done(function(data) {
            if (data.status && data.subjects) {
                renderSubjectTable(data.subjects);
            } else {
                renderSubjectTable([]);
            }
        }).fail(function() {
            renderSubjectTable([]);
        });
    }

    function updateGenderChart(maleCount, femaleCount) {
        var ctx = document.getElementById('chart-gender');
        if (!ctx) return;
        labelMale.textContent = maleCount;
        labelFemale.textContent = femaleCount;

        var data = [maleCount, femaleCount];
        if (chartGender) {
            chartGender.data.datasets[0].data = data;
            chartGender.update();
            return;
        }
        chartGender = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: [maleLabel, femaleLabel],
                datasets: [{
                    data: data,
                    backgroundColor: ['rgba(26, 115, 232, 0.9)', 'rgba(2, 187, 241, 0.9)'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '60%'
            }
        });
    }

    function fetchStats() {
        if (!isRangeValid()) {
            showRangeError(true);
            return;
        }
        showRangeError(false);

        var semesterId = semesterSelect.value;
        var params = { semester_id: semesterId };
        if (dateFromInput.value) params.date_from = dateFromInput.value;
        if (dateToInput.value) params.date_to = dateToInput.value;

        kpiCourses.textContent = '{{ __("app.loading") }}...';
        if (kpiStudents) kpiStudents.textContent = '{{ __("app.loading") }}...';
        if (kpiAcademicLevels) kpiAcademicLevels.textContent = '{{ __("app.loading") }}...';
        statTotal.textContent = '0';
        statCount.textContent = '0';
        if (statStudentsPaid) statStudentsPaid.textContent = '0';
        if (statStudentsTotal) statStudentsTotal.textContent = '0';
        if (labelMale) labelMale.textContent = '0';
        if (labelFemale) labelFemale.textContent = '0';

        $.get(statsUrl, params)
            .done(function(data) {
                if (data.status && data.statistics) {
                    var s = data.statistics;
                    statTotal.textContent = s.formatted_total;
                    statCount.textContent = s.payments_count;
                    if (statStudentsPaid) statStudentsPaid.textContent = s.total_students_paid != null ? s.total_students_paid : '0';
                    if (statStudentsTotal) statStudentsTotal.textContent = s.formatted_total || '0';
                    if (s.male_count != null && s.female_count != null) updateGenderChart(s.male_count, s.female_count);
                }
                if (data.kpi) {
                    kpiCourses.textContent = data.kpi.courses_in_semester;
                    if (kpiAcademicLevels && data.kpi.academic_levels_in_semester != null) kpiAcademicLevels.textContent = data.kpi.academic_levels_in_semester;
                }
                if (data.statistics && kpiStudents && data.statistics.total_students_paid != null) kpiStudents.textContent = data.statistics.total_students_paid;
                updateExportButtons();
                fetchTeacherStats();
                fetchAcademicLevelStats();
                fetchSubjectStats();
            })
            .fail(function() {
                kpiCourses.textContent = '—';
                if (kpiStudents) kpiStudents.textContent = '—';
                if (kpiAcademicLevels) kpiAcademicLevels.textContent = '—';
                statTotal.textContent = '0';
                statCount.textContent = '0';
                if (statStudentsPaid) statStudentsPaid.textContent = '0';
                if (statStudentsTotal) statStudentsTotal.textContent = '0';
                if (labelMale) labelMale.textContent = '0';
                if (labelFemale) labelFemale.textContent = '0';
            });
    }

    function onFilterChange() {
        if (!isRangeValid()) { showRangeError(true); return; }
        showRangeError(false);
        updateSelectedInfo();
        fetchStats();
    }

    var locale = "{{ app()->getLocale() }}";
    var fpFrom = flatpickr(dateFromInput, { locale: locale, dateFormat: 'Y-m-d', allowInput: true, onChange: onFilterChange });
    var fpTo = flatpickr(dateToInput, { locale: locale, dateFormat: 'Y-m-d', allowInput: true, onChange: onFilterChange });
    dateFromInput.addEventListener('change', function() {
        if (fpTo && dateFromInput.value) fpTo.set('minDate', dateFromInput.value);
    });
    dateToInput.addEventListener('change', function() {
        if (fpFrom && dateToInput.value) fpFrom.set('maxDate', dateToInput.value);
    });
    semesterSelect.addEventListener('change', onFilterChange);

    document.getElementById('btn-reset-dates').addEventListener('click', function() {
        if (fpFrom) {
            fpFrom.setDate(null, false);
            fpFrom.set('maxDate', null);
        }
        if (fpTo) {
            fpTo.setDate(null, false);
            fpTo.set('minDate', null);
        }
        showRangeError(false);
        updateExportButtons();
        fetchStats();
    });

    var teacherCard = document.getElementById('teacher-stats-tbody');
    if (teacherCard) {
        teacherCard.closest('.card').querySelectorAll('th.sortable').forEach(function(th) {
            th.style.cursor = 'pointer';
            th.addEventListener('click', function() { sortTeacherStats(th.getAttribute('data-sort')); });
        });
    }

    fetchStats();
    updateSelectedInfo();
    updateExportButtons();

    // Export icon on financial KPI cards (Today / Week / Month / Year)
    document.querySelectorAll('.btn-download-excel').forEach(function(btn) {
        btn.addEventListener('click', function(event) {
            event.stopPropagation();
            var type = btn.getAttribute('data-type');
            var start = btn.getAttribute('data-start');
            var end = btn.getAttribute('data-end');
            $.ajax({
                type: 'POST',
                url: "{{ route('dashboard.export') }}",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { service_type: type, start: start, end: end },
                xhrFields: { responseType: 'blob' },
                success: function(data) {
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }));
                    link.download = (type || 'payments') + ' ' + (start || '') + ' to ' + (end || '') + '.xlsx';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        });
    });
})();
</script>
@endpush
