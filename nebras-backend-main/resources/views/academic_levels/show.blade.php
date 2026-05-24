@extends('layouts.app')
@section('title'){{ __('app.academic levels') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.academic levels') }} - #{{ $academicLevel->id }}</h6>
                </div>
                <div class="card-body">
                    
                    <div class="row mb-3">
                        <label class="d-block fw-bold">{{ __('app.image') }}</label>
                        <div class="col-sm-12">
                            @if ($academicLevel->image)
                                <img src="{{ $academicLevel->image }}" class="img-fluid rounded shadow-sm mx-auto d-block" style="max-height:250px;" alt="">
                            @else
                                <p class="form-control-plaintext">—</p>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.thumbnail_male') }}</label>
                            @if ($academicLevel->thumbnail_male)
                                <img src="{{ $academicLevel->thumbnail_male }}" class="img-fluid rounded shadow-sm" style="max-height:250px;" alt="">
                            @else
                                <p class="form-control-plaintext">—</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.thumbnail_female') }}</label>
                            @if ($academicLevel->thumbnail_female)
                                <img src="{{ $academicLevel->thumbnail_female }}" class="img-fluid rounded shadow-sm" style="max-height:250px;" alt="">
                            @else
                                <p class="form-control-plaintext">—</p>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="d-block fw-bold">{{ __('app.quote icon') }}</label>
                            @php
                                $qColor = $academicLevel->quote_icon_color ?? \App\Models\AcademicLevel::DEFAULT_QUOTE_ICON_COLOR;
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle border d-inline-block" style="width:32px;height:32px;background-color:{{ $qColor }};" title="{{ $qColor }}"></span>
                                <span class="form-control-plaintext mb-0">{{ $qColor }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.title') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $academicLevel->title }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.title') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $academicLevel->title_en }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.description') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $academicLevel->description }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.description') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $academicLevel->description_en }}</p>
                            </div>
                        </div>
                    </div>
                     -->

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.created at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $academicLevel->created_at ? $academicLevel->created_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.updated at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $academicLevel->updated_at ? $academicLevel->updated_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('academic-levels.index') }}" class="btn btn-secondary me-2">{{ __('app.back') }}</a>
                        <a href="{{ route('academic-levels.edit', $academicLevel->id) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
