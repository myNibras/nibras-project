@extends('layouts.teacher')
@section('title'){{ __('app.edit') }} {{ $course->getLocalizationTitle() }} - {{ __('app.courses') }}@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-dark border-radius-lg text-white d-flex justify-content-between align-items-center px-4">
                    <h6 class="mb-0">{{ __('app.edit') }} {{ __('app.courses') }} - {{ $course->getLocalizationTitle() }}</h6>
                    <a href="{{ route('teacher.courses.show', $course->id) }}" class="btn btn-light btn-sm mb-0">
                        <i class="fa-solid fa-reply me-1"></i>{{ __('app.back') }}
                    </a>
                </div>
                <div class="card-body px-0 pb-2">
                    @if ($errors->any())
                        <div class="alert alert-danger mx-4">
                            <ul class="mb-0 text-white">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('teacher.courses.update', $course->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mx-4">

                            <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-2 mt-3">{{ __('app.general information') }}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="title" class="form-label fw-bold">{{ __('app.title') }} (AR)</label>
                                    <input type="text" id="title" class="form-control bg-light" value="{{ $course->title }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="title_en" class="form-label fw-bold">{{ __('app.title') }} (EN)</label>
                                    <input type="text" id="title_en" class="form-control bg-light" value="{{ $course->title_en }}" readonly>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.short description') }} (AR)</label>
                                    <textarea class="form-control bg-light" rows="2" readonly>{{ $course->short_description }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.short description') }} (EN)</label>
                                    <textarea class="form-control bg-light" rows="2" readonly>{{ $course->short_description_en }}</textarea>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.description') }} (AR)</label>
                                    <textarea class="form-control bg-light" rows="3" readonly>{{ $course->description }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.description') }} (EN)</label>
                                    <textarea class="form-control bg-light" rows="3" readonly>{{ $course->description_en }}</textarea>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.duration') }} (AR)</label>
                                    <input type="text" class="form-control bg-light" value="{{ $course->duration }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.duration') }} (EN)</label>
                                    <input type="text" class="form-control bg-light" value="{{ $course->duration_en }}" readonly>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.schedule') }} (AR)</label>
                                    <input type="text" class="form-control bg-light" value="{{ $course->schedule }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.schedule') }} (EN)</label>
                                    <input type="text" class="form-control bg-light" value="{{ $course->schedule_en }}" readonly>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.course type') }}</label>
                                    <input type="text" class="form-control bg-light" value="{{ $course->course_type === 'online' ? __('app.online') : __('app.recorded') }}" readonly>
                                </div>
                                @if($course->course_type === 'online')
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.course link') }}</label>
                                    <input type="text" class="form-control bg-light" value="{{ $course->course_link ?? '—' }}" readonly>
                                </div>
                                @endif
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">{{ __('app.image') }}</label>
                                    @if($course->image)
                                        <img src="{{ $course->image }}" alt="" class="rounded d-block mt-1" style="max-height: 100px;">
                                    @else
                                        <p class="text-secondary text-sm mb-0">—</p>
                                    @endif
                                </div>
                            </div>

                            <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-2 mt-4">{{ __('app.curriculums') }} & {{ __('app.units') }}</h6>
                            <div id="curriculums-wrapper">
                                @foreach($course->curriculums as $cIndex => $curriculum)
                                    <div class="card p-3 mt-3 position-relative curriculum-card" id="curriculum-{{ $cIndex }}">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeCurriculum(this)">
                                            <i class="fa-solid fa-trash"></i> {{ __('app.delete') }}
                                        </button>
                                        <h6 class="fw-bold">{{ __('app.unit') }} <span class="curriculum-card-number">{{ $cIndex + 1 }}</span></h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">{{ __('app.unit title') }} (AR)</label>
                                                <input type="text" name="curriculums[{{ $cIndex }}][title]" class="form-control" value="{{ old("curriculums.{$cIndex}.title", $curriculum->title) }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">{{ __('app.unit title') }} (EN)</label>
                                                <input type="text" name="curriculums[{{ $cIndex }}][title_en]" class="form-control" value="{{ old("curriculums.{$cIndex}.title_en", $curriculum->title_en) }}" required>
                                            </div>
                                        </div>
                                        <div class="mt-3 curriculum-units" id="units-{{ $cIndex }}">
                                            @foreach($curriculum->units as $uIndex => $unit)
                                                <div class="unit-row" id="unit-{{ $cIndex }}-{{ $uIndex }}">
                                                    <div class="row mt-2 align-items-center">
                                                        <div class="col-md-6 mb-2">
                                                            <input type="text" name="curriculums[{{ $cIndex }}][units][{{ $uIndex }}][title]" class="form-control" value="{{ $unit->title }}" placeholder="{{ __('app.lesson title') }} (AR)" required>
                                                        </div>
                                                        <div class="col-md-6 mb-2 d-flex gap-2">
                                                            <input type="text" name="curriculums[{{ $cIndex }}][units][{{ $uIndex }}][title_en]" class="form-control" value="{{ $unit->title_en }}" placeholder="{{ __('app.lesson title') }} (EN)" required>
                                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeUnit(this)"><i class="fa-solid fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="text-end mb-1">
                                                        <button type="button" class="btn btn-link btn-sm py-0" onclick="insertUnitAfter(this)">+ {{ __('app.insert lesson below') }}</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addUnitAtEnd(this)">+ {{ __('app.add new lesson') }}</button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="insertCurriculumAfter(this)">+ {{ __('app.insert unit below') }}</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-success mt-3" onclick="addCurriculum()">+ {{ __('app.add new unit') }}</button>

                            <div class="d-flex justify-content-end mt-4 gap-2 align-items-center">
                                @if(session('impersonate.admin_id'))
                                <span class="text-muted small me-2">{{ __('app.view only mode no changes allowed') }}</span>
                                <button type="button" class="btn btn-primary" disabled>{{ __('app.update') }}</button>
                                @else
                                <a href="{{ route('teacher.courses.show', $course->id) }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('app.update') }}</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let curriculumIndex = {{ $course->curriculums->count() }};

function reindexCurriculums() {
    const wrapper = document.getElementById('curriculums-wrapper');
    if (!wrapper) return;
    const cards = wrapper.querySelectorAll(':scope > .curriculum-card');
    cards.forEach((card, cIdx) => {
        card.id = 'curriculum-' + cIdx;
        const numEl = card.querySelector('.curriculum-card-number');
        if (numEl) numEl.textContent = String(cIdx + 1);
        const unitsEl = card.querySelector('.curriculum-units');
        if (unitsEl) unitsEl.id = 'units-' + cIdx;
        card.querySelectorAll('.unit-row').forEach((row, uIdx) => {
            row.id = 'unit-' + cIdx + '-' + uIdx;
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/^curriculums\[\d+\]\[units\]\[\d+\]/, 'curriculums[' + cIdx + '][units][' + uIdx + ']');
            });
        });
        card.querySelectorAll('[name]').forEach(el => {
            if (el.closest('.unit-row')) return;
            el.name = el.name.replace(/^curriculums\[\d+\]/, 'curriculums[' + cIdx + ']');
        });
    });
    curriculumIndex = cards.length;
}

function buildCurriculumCardHtml() {
    return `
    <div class="card p-3 mt-3 position-relative curriculum-card">
        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeCurriculum(this)">
            <i class="fa-solid fa-trash"></i> {{ __('app.delete') }}
        </button>
        <h6 class="fw-bold">{{ __('app.unit') }} <span class="curriculum-card-number">1</span></h6>
        <div class="row">
            <div class="col-md-6">
                <label class="form-label fw-bold">{{ __('app.unit title') }} (AR)</label>
                <input type="text" name="curriculums[0][title]" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">{{ __('app.unit title') }} (EN)</label>
                <input type="text" name="curriculums[0][title_en]" class="form-control" required>
            </div>
        </div>
        <div class="mt-3 curriculum-units" id="units-temp"></div>
        <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addUnitAtEnd(this)">+ {{ __('app.add new lesson') }}</button>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="insertCurriculumAfter(this)">+ {{ __('app.insert unit below') }}</button>
        </div>
    </div>`;
}

function buildUnitRowHtml() {
    return `
    <div class="unit-row">
        <div class="row mt-2 align-items-center">
            <div class="col-md-6 mb-2">
                <input type="text" name="curriculums[0][units][0][title]" class="form-control" placeholder="{{ __('app.lesson title') }} (AR)" required>
            </div>
            <div class="col-md-6 mb-2 d-flex gap-2">
                <input type="text" name="curriculums[0][units][0][title_en]" class="form-control" placeholder="{{ __('app.lesson title') }} (EN)" required>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeUnit(this)"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>
        <div class="text-end mb-1">
            <button type="button" class="btn btn-link btn-sm py-0" onclick="insertUnitAfter(this)">+ {{ __('app.insert lesson below') }}</button>
        </div>
    </div>`;
}

function addCurriculum() {
    document.getElementById('curriculums-wrapper').insertAdjacentHTML('beforeend', buildCurriculumCardHtml());
    reindexCurriculums();
}

function insertCurriculumAfter(btn) {
    btn.closest('.curriculum-card').insertAdjacentHTML('afterend', buildCurriculumCardHtml());
    reindexCurriculums();
}

function addUnitAtEnd(btn) {
    const unitsEl = btn.closest('.curriculum-card').querySelector('.curriculum-units');
    unitsEl.insertAdjacentHTML('beforeend', buildUnitRowHtml());
    reindexCurriculums();
}

function insertUnitAfter(btn) {
    btn.closest('.unit-row').insertAdjacentHTML('afterend', buildUnitRowHtml());
    reindexCurriculums();
}

function removeCurriculum(btn) {
    btn.closest('.curriculum-card').remove();
    reindexCurriculums();
}

function removeUnit(btn) {
    btn.closest('.unit-row').remove();
    reindexCurriculums();
}

document.querySelector('form').addEventListener('submit', function() {
    reindexCurriculums();
});
</script>
@endpush
@endsection
