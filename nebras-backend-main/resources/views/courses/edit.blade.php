@extends('layouts.app')
@section('title'){{ __('app.courses') }} - {{ __('app.edit') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.courses') }} - {{ __('app.edit') }}</h6>
                        <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger mx-4">
                            <ul class="mb-0 text-white">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('courses.update', $course->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Wizard Tabs --}}
                        <ul class="nav nav-tabs mx-3" id="courseTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="step1-tab" data-bs-toggle="tab" data-bs-target="#step1" type="button">{{ __('app.general information') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="step2-tab" data-bs-toggle="tab" data-bs-target="#step2" type="button">{{ __('app.quick info') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="step3-tab" data-bs-toggle="tab" data-bs-target="#step3" type="button">{{ __('app.detailed description') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="step4-tab" data-bs-toggle="tab" data-bs-target="#step4" type="button">{{ __('app.curriculums') }}</button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3 mx-3" id="courseTabsContent">
                            {{-- STEP 1 --}}
                            <div class="tab-pane fade show active" id="step1" role="tabpanel">
                                <div class="row row-gap-3">
                                    <div class="col-md-6">
                                        <label for="title" class="form-label fw-bold">{{ __('app.title') }} (AR)</label>
                                        <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $course->title) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="title_en" class="form-label fw-bold">{{ __('app.title') }} (EN)</label>
                                        <input type="text" name="title_en" id="title_en" class="form-control" value="{{ old('title_en', $course->title_en) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.short_description') }} (AR)</label>
                                        <textarea name="short_description" class="form-control" rows="2" required>{{ old('short_description', $course->short_description) }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.short_description') }} (EN)</label>
                                        <textarea name="short_description_en" class="form-control" rows="2" required>{{ old('short_description_en', $course->short_description_en) }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.price') }}</label>
                                        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $course->price) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.discount_price') }}</label>
                                        <input type="number" step="0.01" name="discount_price" class="form-control" value="{{ old('discount_price', $course->discount_price) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.available_seats') }}</label>
                                        <input type="number" step="1" min="0" name="available_seats" class="form-control" value="{{ old('available_seats', $course->available_seats) }}" placeholder="{{ __('app.optional') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.payment_type') }}</label>
                                        <select name="payment_type" id="payment_type" class="form-control form-select px-2" required>
                                            <option value="one-off" {{ old('payment_type', $course->payment_type ?? 'one-off') == 'one-off' ? 'selected' : '' }}>One-Off</option>
                                            <option value="monthly" {{ old('payment_type', $course->payment_type) == 'monthly' ? 'selected' : '' }}>Monthly (Installments)</option>
                                            <option value="both" {{ old('payment_type', $course->payment_type) == 'both' ? 'selected' : '' }}>Both</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="semester_months_wrapper" style="display: {{ in_array(old('payment_type', $course->payment_type), ['monthly', 'both']) ? 'block' : 'none' }};">
                                        <label class="form-label fw-bold">{{ __('app.semester_months') }}</label>
                                        <input type="number" step="1" min="1" name="semester_months" id="semester_months" class="form-control" value="{{ old('semester_months', $course->semester_months) }}">
                                    </div>
                                    <div class="col-md-6" id="monthly_amount_wrapper" style="display: {{ in_array(old('payment_type', $course->payment_type), ['monthly', 'both']) ? 'block' : 'none' }};">
                                        <label class="form-label fw-bold">{{ __('app.monthly_amount') }}</label>
                                        <input type="number" step="0.01" min="0" name="monthly_amount" id="monthly_amount" class="form-control" value="{{ old('monthly_amount', $course->monthly_amount) }}" disabled readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.semester') }}</label>
                                        <select name="semester_id" class="form-control form-select px-2" required>
                                            <option value="">{{ __('app.select') }}</option>
                                            @foreach($semesters as $semester)
                                                <option value="{{ $semester->id }}" {{ old('semester_id', $course->semester_id) == $semester->id ? 'selected' : '' }}>
                                                    {{ $semester->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.academic_level') }}</label>
                                        <select name="academic_level_id" class="form-control form-select px-2" required>
                                            <option value="">{{ __('app.select') }}</option>
                                            @foreach($levels as $level)
                                                <option value="{{ $level->id }}" {{ old('academic_level_id', $course->academic_level_id) == $level->id ? 'selected' : '' }}>
                                                    {{ $level->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.class') }}</label>
                                        <select name="class_id" class="form-control form-select px-2" required>
                                            <option value="">{{ __('app.select') }}</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ old('class_id', $course->class_id ?? '') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->getLocalizationName() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.course type') }}</label>
                                        <select name="course_type" id="course_type" class="form-control form-select px-2" required>
                                            <option value="recorded" {{ old('course_type', $course->course_type ?? 'recorded') == 'recorded' ? 'selected' : '' }}>{{ __('app.recorded') }}</option>
                                            <option value="online" {{ old('course_type', $course->course_type ?? '') == 'online' ? 'selected' : '' }}>{{ __('app.online') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="course_link_wrapper" style="display: {{ old('course_type', $course->course_type ?? 'recorded') == 'online' ? 'block' : 'none' }};">
                                        <label class="form-label fw-bold">{{ __('app.course link') }}</label>
                                        <input type="text" name="course_link" id="course_link" class="form-control" value="{{ old('course_link', $course->course_link) }}" maxlength="255" placeholder="https://...">
                                    </div>
                                    <div class="col-md-12">
                                        <label for="image">{{ __('app.image') }} - {{ __('app.size')}} (393x393px)</label>
                                        <input type="file" id="image" name="image" data-default-file="{{ $course->image }}"  data-plugins="dropify" data-height="150" data-allowed-file-extensions="png jpg jpeg webp" />
                                        <small class="mt-2 d-block">Allow (jpg, jpeg, png, webp)</small>
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 2 --}}
                            <div class="tab-pane fade" id="step2" role="tabpanel">
                                <div class="row row-gap-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.duration') }} (AR)</label>
                                        <input type="text" name="duration" class="form-control" value="{{ old('duration', $course->duration) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.duration') }} (EN)</label>
                                        <input type="text" name="duration_en" class="form-control" value="{{ old('duration_en', $course->duration_en) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.schedule') }} (AR)</label>
                                        <input type="text" name="schedule" class="form-control" value="{{ old('schedule', $course->schedule) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.schedule') }} (EN)</label>
                                        <input type="text" name="schedule_en" class="form-control" value="{{ old('schedule_en', $course->schedule_en) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.teacher') }}</label>
                                        <select name="teacher_id" class="form-control form-select px-2" required>
                                            <option value="">{{ __('app.select') }}</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ old('teacher_id', $course->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                                    {{ $teacher->name }} - {{ $teacher->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 3 --}}
                            <div class="tab-pane fade" id="step3" role="tabpanel">
                                <div class="row row-gap-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.detailed description') }} (AR)</label>
                                        <textarea name="description" class="form-control" rows="4">{{ old('description', $course->description) }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ __('app.detailed description') }} (EN)</label>
                                        <textarea name="description_en" class="form-control" rows="4">{{ old('description_en', $course->description_en) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 4: Curriculums --}}
                            <div class="tab-pane fade" id="step4" role="tabpanel">
                                <h6 class="fw-bold">{{ __('app.curriculums') }} & {{ __('app.units') }}</h6>
                                <div id="curriculums-wrapper">
                                    @foreach($course->curriculums as $cIndex => $curriculum)
                                        <div class="card p-3 mt-3 position-relative curriculum-card" id="curriculum-{{ $cIndex }}">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                                    onclick="removeCurriculum(this)">
                                                <i class="fa-solid fa-trash"></i> {{ __('app.delete') }}
                                            </button>
                                            <h6 class="fw-bold">{{ __('app.unit') }} <span class="curriculum-card-number">{{ $cIndex + 1 }}</span></h6>
                                            <input type="hidden" name="curriculums[{{ $cIndex }}][id]" value="{{ $curriculum->id }}">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">{{ __('app.unit title') }} (AR)</label>
                                                    <input type="text" name="curriculums[{{ $cIndex }}][title]" class="form-control"
                                                        value="{{ $curriculum->title }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">{{ __('app.unit title') }} (EN)</label>
                                                    <input type="text" name="curriculums[{{ $cIndex }}][title_en]" class="form-control"
                                                        value="{{ $curriculum->title_en }}" required>
                                                </div>
                                            </div>

                                            <div class="mt-3 curriculum-units" id="units-{{ $cIndex }}">
                                                @foreach($curriculum->units as $uIndex => $unit)
                                                    <div class="unit-row p-2 rounded border mb-2" id="unit-{{ $cIndex }}-{{ $uIndex }}">
                                                        <div class="row mt-2 align-items-center">
                                                            <input type="hidden" name="curriculums[{{ $cIndex }}][units][{{ $uIndex }}][id]" value="{{ $unit->id }}">
                                                            <div class="col-md-6 mb-2">
                                                                <input type="text" name="curriculums[{{ $cIndex }}][units][{{ $uIndex }}][title]"
                                                                    class="form-control" value="{{ $unit->title }}" placeholder="Lesson Title (AR)" required>
                                                            </div>
                                                            <div class="col-md-6 mb-2 d-flex gap-2">
                                                                <input type="text" name="curriculums[{{ $cIndex }}][units][{{ $uIndex }}][title_en]"
                                                                    class="form-control" value="{{ $unit->title_en }}" placeholder="Lesson Title (EN)" required>
                                                                <button type="button" class="btn btn-danger"
                                                                        onclick="removeUnit(this)">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12 mb-2">
                                                                <label class="form-label fw-semibold small mb-1">
                                                                    {{ __('app.external link') }}
                                                                    <span class="text-muted fw-normal">({{ __('app.optional') }})</span>
                                                                </label>
                                                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                                                    <input type="url"
                                                                           name="curriculums[{{ $cIndex }}][units][{{ $uIndex }}][link]"
                                                                           class="form-control flex-fill" style="min-width:240px;"
                                                                           value="{{ $unit->link }}"
                                                                           placeholder="https://...">
                                                                    <div class="form-check form-switch m-0">
                                                                        <input class="form-check-input" type="checkbox"
                                                                               name="curriculums[{{ $cIndex }}][units][{{ $uIndex }}][open_in_new_tab]"
                                                                               value="1" {{ $unit->open_in_new_tab ? 'checked' : '' }}>
                                                                        <label class="form-check-label small">{{ __('app.open in new tab') }}</label>
                                                                    </div>
                                                                    <div class="form-check form-switch m-0">
                                                                        <input class="form-check-input" type="checkbox"
                                                                               name="curriculums[{{ $cIndex }}][units][{{ $uIndex }}][registered_students]"
                                                                               value="1" {{ $unit->registered_students ? 'checked' : '' }}>
                                                                        <label class="form-check-label small">{{ __('app.private') }}</label>
                                                                    </div>
                                                                </div>
                                                                <div class="form-text small">{{ __('app.external link helper') }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="text-end mb-1">
                                                            <button type="button" class="btn btn-link btn-sm py-0" onclick="insertUnitAfter(this)">+ {{ __('app.insert lesson below') }}</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="addUnitAtEnd(this)">
                                                    + {{ __('app.add new lesson') }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                        onclick="insertCurriculumAfter(this)">
                                                    + {{ __('app.insert unit below') }}
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" class="btn btn-success mt-3" onclick="addCurriculum()">
                                    + {{ __('app.add new unit') }}
                                </button>
                            </div>

                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('courses.index') }}" class="btn btn-secondary me-2">{{ __('app.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('app.update') }}</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .unit-row:nth-child(even) {
        background-color: #f3f6fb;
    }
    .unit-row:nth-child(odd) {
        background-color: #ffffff;
    }
</style>

<script>
let curriculumIndex = {{ $course->curriculums->count() }};
let deletedCurriculums = [];
let deletedUnits = [];

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
        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                onclick="removeCurriculum(this)">
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
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addUnitAtEnd(this)">
                + {{ __('app.add new lesson') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="insertCurriculumAfter(this)">
                + {{ __('app.insert unit below') }}
            </button>
        </div>
    </div>`;
}

function buildUnitRowHtml() {
    return `
    <div class="unit-row p-2 rounded border mb-2">
        <div class="row mt-2 align-items-center">
            <div class="col-md-6 mb-2">
                <input type="text" name="curriculums[0][units][0][title]"
                       class="form-control" placeholder="{{ __('app.lesson title') }} (AR)" required>
            </div>
            <div class="col-md-6 mb-2 d-flex gap-2">
                <input type="text" name="curriculums[0][units][0][title_en]"
                       class="form-control" placeholder="{{ __('app.lesson title') }} (EN)" required>
                <button type="button" class="btn btn-danger" onclick="removeUnit(this)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-2">
                <label class="form-label fw-semibold small mb-1">
                    {{ __('app.external link') }} <span class="text-muted fw-normal">({{ __('app.optional') }})</span>
                </label>
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <input type="url" name="curriculums[0][units][0][link]"
                           class="form-control flex-fill" style="min-width:240px;"
                           placeholder="https://...">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox"
                               name="curriculums[0][units][0][open_in_new_tab]" value="1" checked>
                        <label class="form-check-label small">{{ __('app.open in new tab') }}</label>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox"
                               name="curriculums[0][units][0][registered_students]" value="1">
                        <label class="form-check-label small">{{ __('app.private') }}</label>
                    </div>
                </div>
                <div class="form-text small">{{ __('app.external link helper') }}</div>
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
    const card = btn.closest('.curriculum-card');
    card.insertAdjacentHTML('afterend', buildCurriculumCardHtml());
    reindexCurriculums();
}

function addUnitAtEnd(btn) {
    const card = btn.closest('.curriculum-card');
    const unitsEl = card.querySelector('.curriculum-units');
    unitsEl.insertAdjacentHTML('beforeend', buildUnitRowHtml());
    reindexCurriculums();
}

function insertUnitAfter(btn) {
    const row = btn.closest('.unit-row');
    row.insertAdjacentHTML('afterend', buildUnitRowHtml());
    reindexCurriculums();
}

function getCurriculumDbId(card) {
    let id = null;
    card.querySelectorAll('input[type="hidden"][name^="curriculums["]').forEach(inp => {
        if (/^curriculums\[\d+\]\[id\]$/.test(inp.name)) id = parseInt(inp.value, 10);
    });
    return id || null;
}

function getUnitDbId(row) {
    let id = null;
    row.querySelectorAll('input[type="hidden"][name^="curriculums["]').forEach(inp => {
        if (/^curriculums\[\d+\]\[units\]\[\d+\]\[id\]$/.test(inp.name)) id = parseInt(inp.value, 10);
    });
    return id || null;
}

function removeCurriculum(btn) {
    const card = btn.closest('.curriculum-card');
    const id = getCurriculumDbId(card);
    if (id) deletedCurriculums.push(id);
    card.remove();
    reindexCurriculums();
}

function removeUnit(btn) {
    const row = btn.closest('.unit-row');
    const id = getUnitDbId(row);
    if (id) deletedUnits.push(id);
    row.remove();
    reindexCurriculums();
}

document.querySelector('form').addEventListener('submit', function() {
    reindexCurriculums();
    deletedCurriculums.forEach(id => {
        this.insertAdjacentHTML('beforeend', `<input type="hidden" name="deleted_curriculums[]" value="${id}">`);
    });
    deletedUnits.forEach(id => {
        this.insertAdjacentHTML('beforeend', `<input type="hidden" name="deleted_units[]" value="${id}">`);
    });
});

// Payment Type Change Handler
document.addEventListener('DOMContentLoaded', function() {
    const paymentType = document.getElementById('payment_type');
    const semesterMonthsWrapper = document.getElementById('semester_months_wrapper');
    const monthlyAmountWrapper = document.getElementById('monthly_amount_wrapper');
    const semesterMonths = document.getElementById('semester_months');
    const monthlyAmount = document.getElementById('monthly_amount');
    const priceInput = document.querySelector('input[name="price"]');
    const discountPriceInput = document.querySelector('input[name="discount_price"]');

    function togglePaymentFields() {
        const value = paymentType.value;
        if (value === 'monthly' || value === 'both') {
            semesterMonthsWrapper.style.display = 'block';
            monthlyAmountWrapper.style.display = 'block';
            semesterMonths.setAttribute('required', 'required');
            monthlyAmount.setAttribute('required', 'required');
        } else {
            semesterMonthsWrapper.style.display = 'none';
            monthlyAmountWrapper.style.display = 'none';
            semesterMonths.removeAttribute('required');
            monthlyAmount.removeAttribute('required');
        }
    }

    function calculateMonthlyAmount() {
        const value = paymentType.value;
        if ((value === 'monthly' || value === 'both') && priceInput.value && semesterMonths.value) {
            // Use discount_price if it has a value, otherwise use price
            const discountPrice = parseFloat(discountPriceInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const finalPrice = discountPrice > 0 ? discountPrice : price;
            const months = parseInt(semesterMonths.value) || 1;
            if (months > 0 && finalPrice > 0) {
                monthlyAmount.value = (finalPrice / months).toFixed(2);
            }
        }
    }

    paymentType.addEventListener('change', function() {
        togglePaymentFields();
        calculateMonthlyAmount();
    });

    priceInput.addEventListener('input', calculateMonthlyAmount);
    discountPriceInput.addEventListener('input', calculateMonthlyAmount);
    semesterMonths.addEventListener('input', calculateMonthlyAmount);

    // Initialize on page load
    togglePaymentFields();
    if (paymentType.value) {
        calculateMonthlyAmount();
    }

    // Course Type: show Course Link only when "online"
    const courseType = document.getElementById('course_type');
    const courseLinkWrapper = document.getElementById('course_link_wrapper');
    const courseLinkInput = document.getElementById('course_link');
    function toggleCourseLink() {
        if (courseType.value === 'online') {
            courseLinkWrapper.style.display = 'block';
            courseLinkInput.setAttribute('required', 'required');
        } else {
            courseLinkWrapper.style.display = 'none';
            courseLinkInput.removeAttribute('required');
            courseLinkInput.value = '';
        }
    }
    courseType.addEventListener('change', toggleCourseLink);
    toggleCourseLink();
});
</script>
@endsection
