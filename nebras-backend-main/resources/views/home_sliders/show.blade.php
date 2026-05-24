@extends('layouts.app')
@section('title'){{ __('app.home sliders') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.home sliders') }} - #{{ $homeSlider->id }}</h6>
                </div>
                <div class="card-body">

                    <div class="row mb-3">
                        <label class="d-block fw-bold">{{ __('app.image') }}</label>
                        <div class="col-sm-12">
                            <img src="{{ $homeSlider->image }}" class="img-fluid rounded shadow-sm mx-auto d-block" style="max-height:250px;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.title') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $homeSlider->title }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.title') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $homeSlider->title_en }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.description') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $homeSlider->description }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.description') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $homeSlider->description_en }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.button title') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $homeSlider->button_title }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.button title') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $homeSlider->button_title_en }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.button link') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">
                                    <a href="{{ $homeSlider->button_link }}" target="_blank">{{ $homeSlider->button_link }}</a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.button link') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">
                                    <a href="{{ $homeSlider->button_link_en }}" target="_blank">{{ $homeSlider->button_link_en }}</a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                        <label class="d-block fw-bold">{{ __('app.created at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $homeSlider->created_at ? $homeSlider->created_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.updated at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $homeSlider->updated_at ? $homeSlider->updated_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('home-sliders.index') }}" class="btn btn-secondary me-2">{{ __('app.back') }}</a>
                        <a href="{{ route('home-sliders.edit', $homeSlider->id) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
