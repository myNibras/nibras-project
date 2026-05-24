@extends('layouts.app')
@section('title'){{ __('app.classes') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.classes') }} - #{{ $class->id }}</h6>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.name') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $class->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.name') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $class->name_en }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.academic_level') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $class->academicLevel ? $class->academicLevel->getLocalizationTitle() : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.created at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $class->created_at ? $class->created_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.updated at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $class->updated_at ? $class->updated_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('classes.index') }}" class="btn btn-secondary me-2">{{ __('app.back') }}</a>
                        <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
