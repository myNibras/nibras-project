@extends('layouts.app')
@section('title'){{ __('app.courses') }} - {{ $course->title_en }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-12 col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.courses') }} - #{{ $course->id }}</h6>
                </div>
                <div class="card-body">

                    {{-- General Info --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.title') }} (AR)</label>
                            <p class="form-control-plaintext">{{ $course->title }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.title') }} (EN)</label>
                            <p class="form-control-plaintext">{{ $course->title_en }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.short_description') }} (AR)</label>
                            <p class="form-control-plaintext">{{ $course->short_description }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.short_description') }} (EN)</label>
                            <p class="form-control-plaintext">{{ $course->short_description_en }}</p>
                        </div>
                    </div>

                    {{-- Detailed Description --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.detailed description') }} (AR)</label>
                            <p class="form-control-plaintext">{{ $course->description }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.detailed description') }} (EN)</label>
                            <p class="form-control-plaintext">{{ $course->description_en }}</p>
                        </div>
                    </div>

                    {{-- Quick Info --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.duration') }} (AR)</label>
                            <p class="form-control-plaintext">{{ $course->duration }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.duration') }} (EN)</label>
                            <p class="form-control-plaintext">{{ $course->duration_en }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.schedule') }} (AR)</label>
                            <p class="form-control-plaintext">{{ $course->schedule }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.schedule') }} (EN)</label>
                            <p class="form-control-plaintext">{{ $course->schedule_en }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.available_seats') }}</label>
                            <p class="form-control-plaintext">{{ $course->available_seats ?? '—' }}</p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.semester') }}</label>
                            <p class="form-control-plaintext">{{ $course->semester->title ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.academic_level') }}</label>
                            <p class="form-control-plaintext">{{ $course->academicLevel->title ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.course type') }}</label>
                            <p class="form-control-plaintext">{{ $course->course_type === 'online' ? __('app.online') : __('app.recorded') }}</p>
                        </div>
                        @if($course->course_type === 'online' && $course->course_link)
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.course link') }}</label>
                            <p class="form-control-plaintext"><a href="{{ $course->course_link }}" target="_blank" rel="noopener">{{ $course->course_link }}</a></p>
                        </div>
                        @endif
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.price') }}</label>
                            <p class="form-control-plaintext">{{ "$".(new \App\Helpers\Helper)->formatNumber($course->price) }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.discount_price') }}</label>
                            <p class="form-control-plaintext">{{ $course->discount_price ? "$".(new \App\Helpers\Helper)->formatNumber($course->discount_price) : '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.payment_type') }}</label>
                            <p class="form-control-plaintext">
                                @if($course->payment_type == 'one-off')
                                    One-Off
                                @elseif($course->payment_type == 'monthly')
                                    Monthly (Installments)
                                @elseif($course->payment_type == 'both')
                                    Both
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.semester_months') }}</label>
                            <p class="form-control-plaintext">{{ $course->semester_months ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.monthly_amount') }}</label>
                            <p class="form-control-plaintext">{{ $course->monthly_amount ? "$".(new \App\Helpers\Helper)->formatNumber($course->monthly_amount) : '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.teacher') }}</label>
                            <p class="form-control-plaintext">{{ $course->teacher->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">{{ __('app.class') }}</label>
                            <p class="form-control-plaintext">{{ $course->classRoom?->getLocalizationName() }}</p>
                        </div>
                    </div>

                    {{-- Curriculums & Units --}}
                    <h6 class="fw-bold mt-4">{{ __('app.curriculums') }} & {{ __('app.units') }}</h6>
                    @foreach($course->curriculums as $curriculum)
                        <div class="card p-3 mb-2">
                            <h6>{{ $curriculum->title }} / {{ $curriculum->title_en }}</h6>
                            <ul>
                                @foreach($curriculum->units as $unit)
                                    <li>{{ $unit->title }} / {{ $unit->title_en }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('courses.index') }}" class="btn btn-secondary me-2">{{ __('app.back') }}</a>
                        <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
