@extends('layouts.teacher')
@section('title'){{ __('app.dashboard') }} - {{ __('app.teachers') }}@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-3">
            <h4 class="font-weight-bolder mb-1">{{ __('app.dashboard') }}</h4>
            <p class="text-sm text-secondary mb-0">{{ $teacher->getLocalizationName() }}</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('teacher.dashboard') }}" id="dashboard-filter-form">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label for="semester_id" class="form-label">{{ __('app.select semester') }}</label>
                        <select id="semester_id" name="semester_id" class="form-select">
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ (int) $semesterId === (int) $semester->id ? 'selected' : '' }}>
                                    {{ $semester->getLocalizationTitle() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="date_from" class="form-label">{{ __('app.date from') }}</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $dateFromInput ?? '' }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="date_to" class="form-label">{{ __('app.date to') }}</label>
                        <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $dateToInput ?? '' }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-sm text-uppercase text-secondary mb-1">{{ __('app.today') }}</p>
                    <h4 class="mb-0 font-weight-bolder">$<span id="kpi-today">{{ $stats['today'] }}</span></h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-sm text-uppercase text-secondary mb-1">{{ __('app.month') }}</p>
                    <h4 class="mb-0 font-weight-bolder">$<span id="kpi-month">{{ $stats['month'] }}</span></h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-sm text-uppercase text-secondary mb-1">{{ __('app.semester') }}</p>
                    <h4 class="mb-0 font-weight-bolder">$<span id="kpi-semester">{{ $stats['semester'] }}</span></h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-sm text-uppercase text-secondary mb-1">{{ __('app.total revenue') }}</p>
                    <h4 class="mb-0 font-weight-bolder">$<span id="kpi-total-revenue">{{ $stats['total_revenue'] }}</span></h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('dashboard-filter-form');
        const statsUrl = @json(route('teacher.dashboard.stats'));
        if (!form || !statsUrl) return;

        const semester = document.getElementById('semester_id');
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        const kpiToday = document.getElementById('kpi-today');
        const kpiMonth = document.getElementById('kpi-month');
        const kpiSemester = document.getElementById('kpi-semester');
        const kpiTotalRevenue = document.getElementById('kpi-total-revenue');

        let requestCounter = 0;

        const buildQuery = () => {
            const params = new URLSearchParams();
            if (semester && semester.value) {
                params.set('semester_id', semester.value);
            }
            if (dateFrom && dateFrom.value) {
                params.set('date_from', dateFrom.value);
            }
            if (dateTo && dateTo.value) {
                params.set('date_to', dateTo.value);
            }

            return params;
        };

        const fetchStats = async () => {
            const currentRequest = ++requestCounter;
            const params = buildQuery();

            try {
                const response = await fetch(`${statsUrl}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                if (!data.status || !data.stats || currentRequest !== requestCounter) {
                    return;
                }

                if (kpiToday) kpiToday.textContent = data.stats.today ?? '0';
                if (kpiMonth) kpiMonth.textContent = data.stats.month ?? '0';
                if (kpiSemester) kpiSemester.textContent = data.stats.semester ?? '0';
                if (kpiTotalRevenue) kpiTotalRevenue.textContent = data.stats.total_revenue ?? '0';
            } catch (error) {
                // Ignore transient network errors and keep current values.
            }
        };

        [semester, dateFrom, dateTo].forEach((element) => {
            if (!element) return;
            element.addEventListener('change', fetchStats);
        });
    });
</script>
@endpush

