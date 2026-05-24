@extends('layouts.app')
@section('title'){{ __('app.semesters') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.semesters') }} - #{{ $semester->id }}</h6>
                </div>
                <div class="card-body">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.title') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $semester->title }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.title') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $semester->title_en }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.semester type') }}</label>
                            <div>
                                <p class="form-control-plaintext">
                                    @if($semester->type == 1)
                                        {{ __('semester one') }}
                                    @elseif($semester->type == 2)
                                        {{ __('app.semester two') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.status') }}</label>
                            <div>
                                <p class="form-control-plaintext">
                                    @if($semester->status)
                                        <span class="badge bg-success">{{ __('app.active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('app.inactive') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.created at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $semester->created_at ? $semester->created_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.updated at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $semester->updated_at ? $semester->updated_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('semesters.index') }}" class="btn btn-secondary me-2">{{ __('app.back') }}</a>
                        <a href="{{ route('semesters.edit', $semester->id) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
