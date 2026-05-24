@extends('layouts.app')
@section('title'){{ __('app.home sliders') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">
                            {{ __('app.home sliders') }} - {{ __('app.edit')." #".$homeSlider->id }}
                            <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                        </h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger mx-4">
                            <ul class="mb-0 text-white">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('home-sliders.update', $homeSlider->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row">
                                {{-- Title (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="title" class="form-label fw-bold">{{ __('app.title') }} (AR)</label>
                                    <input type="text" name="title" id="title" 
                                        class="form-control"
                                        value="{{ old('title', $homeSlider->title) }}">
                                </div>

                                {{-- Title (English) --}}
                                <div class="col-md-6">
                                    <label for="title_en" class="form-label fw-bold">{{ __('app.title') }} (EN)</label>
                                    <input type="text" name="title_en" id="title_en" 
                                        class="form-control"
                                        value="{{ old('title_en', $homeSlider->title_en) }}">
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Description (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="description" class="form-label fw-bold">{{ __('app.description') }} (AR)</label>
                                    <textarea name="description" id="description" rows="3" 
                                        class="form-control">{{ old('description', $homeSlider->description) }}</textarea>
                                </div>

                                {{-- Description (English) --}}
                                <div class="col-md-6">
                                    <label for="description_en" class="form-label fw-bold">{{ __('app.description') }} (EN)</label>
                                    <textarea name="description_en" id="description_en" rows="3" 
                                        class="form-control">{{ old('description_en', $homeSlider->description_en) }}</textarea>
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Button title (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="button_title" class="form-label fw-bold">{{ __('app.button title') }} (AR)</label>
                                    <input type="text" name="button_title" id="button_title" 
                                        class="form-control"
                                        value="{{ old('button_title', $homeSlider->button_title) }}">
                                </div>

                                {{-- Button Title (English) --}}
                                <div class="col-md-6">
                                    <label for="button_title_en" class="form-label fw-bold">{{ __('app.button title') }} (EN)</label>
                                    <input type="text" name="button_title_en" id="button_title_en" 
                                        class="form-control"
                                        value="{{ old('button_title_en', $homeSlider->button_title_en) }}">
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Button link (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="button_link" class="form-label fw-bold">{{ __('app.button link') }} (AR)</label>
                                    <input type="text" name="button_link" id="button_link" 
                                        class="form-control"
                                        value="{{ old('button_link', $homeSlider->button_link) }}">
                                </div>

                                {{-- Button link (English) --}}
                                <div class="col-md-6">
                                    <label for="button_link_en" class="form-label fw-bold">{{ __('app.button link') }} (EN)</label>
                                    <input type="text" name="button_link_en" id="button_link_en" 
                                        class="form-control"
                                        value="{{ old('button_link_en', $homeSlider->button_link_en) }}">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label for="image">{{ __('app.image') }} - {{ __('app.size')}} (393x575px)</label>
                                    <input type="file" id="image" name="image"
                                        data-plugins="dropify"
                                        data-height="150"
                                        data-default-file="{{ $homeSlider->getImageAttribute() }}"
                                        data-allowed-file-extensions="png jpg jpeg webp" />
                                    <small class="mt-2 d-block">Allow (jpg, jpeg, png, webp)</small>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('home-sliders.index') }}" class="btn btn-secondary me-2">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('app.update') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
