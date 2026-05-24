@extends('layouts.teacher')
@section('title'){{ __('app.dashboard') }}@endsection

@section('content')
<div class="container-fluid py-4 px-3">
    <div class="row mb-4">
        <div class="col">
            <h3 class="mb-1 h4 font-weight-bolder">{{ __('app.dashboard') }}</h3>
            <p class="text-sm text-muted mb-0">{{ __('app.semester dashboard description') }}</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
                    <p class="text-uppercase text-xs text-secondary mb-1">{{ __('app.today') }}</p>
                    <h4 class="mb-0 font-weight-bolder">${{ (new \App\Helpers\Helper)->formatNumber($today['today']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
                    <p class="text-uppercase text-xs text-secondary mb-1">{{ __('app.week') }}</p>
                    <h4 class="mb-0 font-weight-bolder">${{ (new \App\Helpers\Helper)->formatNumber($week['current_total']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
                    <p class="text-uppercase text-xs text-secondary mb-1">{{ __('app.month') }}</p>
                    <h4 class="mb-0 font-weight-bolder">${{ (new \App\Helpers\Helper)->formatNumber($month['current_total']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
                    <p class="text-uppercase text-xs text-secondary mb-1">{{ __('app.year') }}</p>
                    <h4 class="mb-0 font-weight-bolder">${{ (new \App\Helpers\Helper)->formatNumber($year['current_total']) }}</h4>
                </div>
            </div>
        </div>
    </div>

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

    <h6 class="text-uppercase text-secondary font-weight-bolder mb-3 pb-2 border-bottom">{{ __('app.statistics') }}</h6>
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-4">
                    <h6 class="text-secondary mb-1">{{ __('app.courses in semester') }}</h6>
                    <h4 class="mb-0 font-weight-bolder" id="kpi-courses">{{ __('app.loading') }}...</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-4">
                    <h6 class="text-secondary mb-1">{{ __('app.students') }}</h6>
                    <h4 class="mb-0 font-weight-bolder" id="kpi-students">{{ __('app.loading') }}...</h4>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-4">
                    <h6 class="text-secondary mb-1">{{ __('app.total revenue') }}</h6>
                    <h4 class="mb-0 font-weight-bolder">$<span id="stat-total">0</span></h4>
                    <p class="text-xs text-muted mt-2 mb-0">{{ __('app.payments count') }}: <strong id="stat-count">0</strong></p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-4">
                    <h6 class="text-secondary mb-1">Best class in revenue</h6>
                    <h5 class="mb-1 font-weight-bolder" id="kpi-best-class-name">—</h5>
                    <p class="text-xs text-muted mb-0">$<span id="kpi-best-class-amount">0</span></p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
<script>
(function() {
    var statsUrl = "{{ route('teacher.dashboard.semester.stats') }}";
    var semesterSelect = document.getElementById('semester-select');
    var dateFromInput = document.getElementById('date-from');
    var dateToInput = document.getElementById('date-to');
    var dateRangeError = document.getElementById('date-range-error');
    var kpiCourses = document.getElementById('kpi-courses');
    var kpiStudents = document.getElementById('kpi-students');
    var statTotal = document.getElementById('stat-total');
    var statCount = document.getElementById('stat-count');
    var kpiBestClassName = document.getElementById('kpi-best-class-name');
    var kpiBestClassAmount = document.getElementById('kpi-best-class-amount');

    function isRangeValid() {
        var from = dateFromInput.value ? new Date(dateFromInput.value) : null;
        var to = dateToInput.value ? new Date(dateToInput.value) : null;
        if (!from || !to) return true;
        return from.getTime() <= to.getTime();
    }

    function fetchStats() {
        if (!isRangeValid()) {
            dateRangeError.classList.remove('d-none');
            return;
        }
        dateRangeError.classList.add('d-none');
        var params = { semester_id: semesterSelect.value };
        if (dateFromInput.value) params.date_from = dateFromInput.value;
        if (dateToInput.value) params.date_to = dateToInput.value;
        $.get(statsUrl, params).done(function(data) {
            if (!(data.status && data.statistics)) return;
            var s = data.statistics;
            statTotal.textContent = s.formatted_total;
            statCount.textContent = s.payments_count;
            kpiStudents.textContent = s.total_students_paid || 0;
            if (data.kpi) {
                kpiCourses.textContent = data.kpi.courses_in_semester || 0;
            }
            if (data.best_class) {
                if (kpiBestClassName) kpiBestClassName.textContent = data.best_class.name || '—';
                if (kpiBestClassAmount) kpiBestClassAmount.textContent = data.best_class.formatted_amount || '0';
            }
        });
    }

    var locale = "{{ app()->getLocale() }}";
    var fpFrom = flatpickr(dateFromInput, { locale: locale, dateFormat: 'Y-m-d', allowInput: true, onChange: fetchStats });
    var fpTo = flatpickr(dateToInput, { locale: locale, dateFormat: 'Y-m-d', allowInput: true, onChange: fetchStats });
    semesterSelect.addEventListener('change', fetchStats);
    document.getElementById('btn-reset-dates').addEventListener('click', function() {
        fpFrom.setDate(null, false);
        fpTo.setDate(null, false);
        fetchStats();
    });

    fetchStats();
})();
</script>
@endpush

