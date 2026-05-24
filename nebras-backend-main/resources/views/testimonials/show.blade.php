@extends('layouts.app')
@section('title'){{ __('app.testimonials') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.testimonials') }} - #{{ $testimonial->id }}</h6>
                </div>
                <div class="card-body">

                    @if($testimonial->image)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="d-block fw-bold">{{ __('app.image') }}</label>
                            <div>
                                <img src="{{ $testimonial->image }}" alt="{{ $testimonial->name }}" style="max-height: 200px; border-radius: 5px;">
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.name') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $testimonial->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.status') }}</label>
                            <div>
                                @if($testimonial->status == 'approved')
                                    <span class="badge bg-success">{{ __('app.approved') }}</span>
                                @elseif($testimonial->status == 'rejected')
                                    <span class="badge bg-danger">{{ __('app.rejected') }}</span>
                                @else
                                    <span class="badge bg-warning">{{ __('app.pending') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="d-block fw-bold">{{ __('app.text') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $testimonial->text }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.class') }}</label>
                            <div>
                                <p class="form-control-plaintext">
                                    {{ $testimonial->classRoom ? $testimonial->classRoom->getLocalizationName() : '-' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.course name') }}</label>
                            <div>
                                <p class="form-control-plaintext">
                                    {{ $testimonial->course ? $testimonial->course->getLocalizationTitleWithTeacher() : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.rate') }}</label>
                            <div>
                                <p class="form-control-plaintext">
                                    {{ $testimonial->rate ?? 0 }}/5
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.created at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $testimonial->created_at ? $testimonial->created_at->format('Y-m-d H:i') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.updated at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $testimonial->updated_at ? $testimonial->updated_at->format('Y-m-d H:i') : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ $testimonial->created_type == 'admin' ? route('testimonials.admins') : route('testimonials.students') }}" class="btn btn-secondary me-2">{{ __('app.back') }}</a>
                        <a href="{{ route('testimonials.edit', $testimonial->id) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

