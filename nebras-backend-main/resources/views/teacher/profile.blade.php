@extends('layouts.teacher')
@section('title'){{ __('app.profile') }} - {{ __('app.teachers') }}@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-dark border-radius-lg text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0">{{ __('app.profile') }}</h6>
                    <a href="{{ route('teacher.profile.edit') }}" class="btn btn-light btn-sm mb-0">
                        <i class="fa-solid fa-pen-to-square me-1"></i>{{ __('app.edit') }}
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-lg-4 col-md-5 text-center mb-4 mb-md-0">
                            @if($teacher->image)
                                <img src="{{ $teacher->image }}" alt="{{ $teacher->getLocalizationName() }}" class="rounded shadow-lg" style="max-height: 280px; width: auto;">
                            @else
                                <div class="bg-light rounded d-inline-flex align-items-center justify-content-center" style="width: 200px; height: 200px;">
                                    <i class="material-symbols-rounded text-secondary" style="font-size: 80px;">person</i>
                                </div>
                            @endif
                            @if($teacher->video_embed_url)
                                <div class="mt-3">
                                    <iframe src="{{ $teacher->video_embed_url }}" class="rounded"
                                        style="width: 100%; max-width: 320px; height: 180px; border: 0;"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                                </div>
                            @elseif($teacher->video)
                                <div class="mt-3">
                                    <video src="{{ $teacher->video }}" controls class="rounded" style="max-width: 100%; max-height: 180px;"></video>
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-8 col-md-7">
                            <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-3">{{ __('app.general information') }}</h6>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <p class="text-xs text-secondary mb-0">{{ __('app.name') }} (AR)</p>
                                    <p class="text-sm font-weight-bold mb-0">{{ $teacher->name ?? '—' }}</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <p class="text-xs text-secondary mb-0">{{ __('app.name') }} (EN)</p>
                                    <p class="text-sm font-weight-bold mb-0">{{ $teacher->name_en ?? '—' }}</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <p class="text-xs text-secondary mb-0">{{ __('app.email') }}</p>
                                    <p class="text-sm font-weight-bold mb-0">{{ $teacher->email ?? '—' }}</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <p class="text-xs text-secondary mb-0">{{ __('app.position') }}</p>
                                    <p class="text-sm font-weight-bold mb-0">{{ $teacher->position ? $teacher->position->getLocalizationName() : '—' }}</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <p class="text-xs text-secondary mb-0">{{ __('app.years of experience') }}</p>
                                    <p class="text-sm font-weight-bold mb-0">{{ $teacher->years_of_experience ?? 0 }}</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <p class="text-xs text-secondary mb-0">{{ __('app.status') }}</p>
                                    <p class="text-sm font-weight-bold mb-0">
                                        @if($teacher->status)
                                            <span class="badge bg-success">{{ __('app.active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if($teacher->description || $teacher->description_en)
                                <hr class="my-3">
                                <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-2">{{ __('app.description') }}</h6>
                                <p class="text-sm mb-0">{{ $teacher->getLocalizationDescription() ?? '—' }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
