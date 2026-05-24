@extends('layouts.teacher')
@section('title'){{ __('app.courses') }} - {{ __('app.teachers') }}@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-dark border-radius-lg text-white">
                    <h6 class="mb-0">{{ __('app.courses') }}</h6>
                </div>
                <div class="card-body">
                    @if($courses->isEmpty())
                        <p class="mb-0">{{ __('app.no results found') }}</p>
                    @else
                        <div class="row g-4">
                            @foreach($courses as $course)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="card h-100 shadow-sm">
                                        @if($course->image)
                                            <img src="{{ $course->image }}" class="card-img-top" alt="{{ $course->getLocalizationTitle() }}" style="height: 180px; object-fit: cover;">
                                        @else
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                                <i class="material-symbols-rounded text-secondary" style="font-size: 64px;">book_ribbon</i>
                                            </div>
                                        @endif
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title text-dark mb-2">{{ $course->getLocalizationTitle() }}</h6>
                                            <p class="card-text text-sm text-secondary flex-grow-1 mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ $course->getLocalizationShortDescription() ?: $course->getLocalizationDescription() ?: '—' }}
                                            </p>
                                            <div class="text-xs text-secondary mb-2">
                                                <span class="d-block"><strong>{{ __('app.academic level') }}:</strong> {{ $course->academicLevel ? $course->academicLevel->getLocalizationTitle() : '—' }}</span>
                                                <span class="d-block"><strong>{{ __('app.semester') }}:</strong> {{ $course->semester ? $course->semester->getLocalizationTitle() : '—' }}</span>
                                                <span class="d-block"><strong>{{ __('app.class') }}:</strong> {{ $course->classRoom ? $course->classRoom->getLocalizationName() : '—' }}</span>
                                            </div>
                                            <div class="mb-2">
                                                @if($course->status)
                                                    <span class="badge bg-success">{{ __('app.active') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                                @endif
                                            </div>
                                            <a href="{{ route('teacher.courses.show', $course->id) }}" class="btn btn-sm bg-gradient-dark mt-auto">{{ __('app.view') }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
