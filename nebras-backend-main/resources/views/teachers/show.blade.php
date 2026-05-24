@extends('layouts.app')
@section('title'){{ __('app.teachers') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-white">{{ __('app.teachers') }} - #{{ $teacher->id }}</h6>
                    <a href="{{ route('teachers.edit', $teacher->id) }}" class="btn btn-light btn-sm mb-0">
                        <i class="fa-solid fa-pen-to-square me-1"></i>{{ __('app.edit') }}
                    </a>
                </div>
                <div class="card-body">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.position') }}</label>
                            <div>
                                <p class="form-control-plaintext">
                                    {{ $teacher->position ? $teacher->position->getLocalizationName() : '-' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.years of experience') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $teacher->years_of_experience ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.description') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $teacher->description ?: '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.description') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $teacher->description_en ?: '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.image') }}</label>
                            <div>
                                @if($teacher->image)
                                    <img src="{{ $teacher->image }}" alt="{{ $teacher->getLocalizationName() }}" style="width: 275px; height: 325px; object-fit: cover;">
                                @else
                                    <p class="form-control-plaintext">-</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.video') }}</label>
                            <div>
                                @if($teacher->video)
                                    <video src="{{ $teacher->video }}" controls style="max-width: 100%; max-height: 300px;"></video>
                                @else
                                    <p class="form-control-plaintext">-</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.name') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $teacher->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.name') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $teacher->name_en }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.created at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $teacher->created_at ? $teacher->created_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.updated at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $teacher->updated_at ? $teacher->updated_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.status') }}</label>
                            <div>
                                <p class="form-control-plaintext">
                                    @if($teacher->status)
                                        <span class="badge bg-success">{{ __('app.active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('app.inactive') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('teachers.index') }}" class="btn btn-secondary me-2">{{ __('app.back') }}</a>
                        <a href="{{ route('teachers.edit', $teacher->id) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
