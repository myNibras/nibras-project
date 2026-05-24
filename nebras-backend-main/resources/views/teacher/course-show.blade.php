@extends('layouts.teacher')
@section('title'){{ $course->getLocalizationTitle() }} - {{ __('app.courses') }}@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-dark border-radius-lg text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0">{{ $course->getLocalizationTitle() }}</h6>
                    <a href="{{ route('teacher.courses.edit', $course->id) }}" class="btn btn-light btn-sm mb-0">
                        <i class="fa-solid fa-pen-to-square me-1"></i>{{ __('app.edit') }} {{ __('app.curriculums') }}
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-4 text-center mb-3">
                            @if($course->image)
                                <img src="{{ $course->image }}" alt="{{ $course->getLocalizationTitle() }}" class="rounded shadow" style="max-height: 220px; width: auto;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 180px;">
                                    <i class="material-symbols-rounded text-secondary" style="font-size: 64px;">book_ribbon</i>
                                </div>
                            @endif
                            <p class="mt-2 mb-0">
                                @if($course->status)
                                    <span class="badge bg-success">{{ __('app.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-2">{{ __('app.general information') }}</h6>
                            <p class="text-sm"><strong>{{ __('app.title') }}:</strong> {{ $course->getLocalizationTitle() }}</p>
                            <p class="text-sm"><strong>{{ __('app.short description') }}:</strong> {{ $course->getLocalizationShortDescription() ?: '—' }}</p>
                            <p class="text-sm"><strong>{{ __('app.academic level') }}:</strong> {{ $course->academicLevel ? $course->academicLevel->getLocalizationTitle() : '—' }}</p>
                            <p class="text-sm"><strong>{{ __('app.semester') }}:</strong> {{ $course->semester ? $course->semester->getLocalizationTitle() : '—' }}</p>
                            <p class="text-sm"><strong>{{ __('app.class') }}:</strong> {{ $course->classRoom ? $course->classRoom->getLocalizationName() : '—' }}</p>
                            <p class="text-sm"><strong>{{ __('app.course type') }}:</strong> {{ $course->course_type === 'online' ? __('app.online') : __('app.recorded') }}</p>
                            @if($course->course_type === 'online' && $course->course_link)
                            <p class="text-sm"><strong>{{ __('app.course link') }}:</strong> <a href="{{ $course->course_link }}" target="_blank" rel="noopener">{{ $course->course_link }}</a></p>
                            @endif
                            <p class="text-sm"><strong>{{ __('app.duration') }}:</strong> {{ $course->getLocalizationDuration() ?: '—' }}</p>
                            <p class="text-sm"><strong>{{ __('app.schedule') }}:</strong> {{ $course->getLocalizationSchedule() ?: '—' }}</p>
                            @if($course->getLocalizationDescription())
                                <p class="text-sm mt-2"><strong>{{ __('app.description') }}:</strong><br>{{ $course->getLocalizationDescription() }}</p>
                            @endif
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold mb-3">{{ __('app.curriculums') }} & {{ __('app.units') }}</h6>
                    @forelse($course->curriculums as $curriculum)
                        <div class="card p-3 mb-2">
                            <h6 class="mb-2">{{ $curriculum->getLocalizationTitle() }}</h6>
                            <ul class="mb-0 ps-3">
                                @foreach($curriculum->units as $unit)
                                    <li>{{ $unit->getLocalizationTitle() }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <p class="text-secondary mb-0">{{ __('app.no results found') }}</p>
                    @endforelse

                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ route('teacher.courses') }}" class="btn btn-secondary">{{ __('app.back') }}</a>
                        <a href="{{ route('teacher.courses.edit', $course->id) }}" class="btn btn-primary">{{ __('app.edit') }} {{ __('app.curriculums') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
