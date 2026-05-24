@extends('layouts.app')
@section('title'){{ __('app.academic levels') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.academic levels') }} - {{ __('app.edit')." #".$academicLevel->id }}</h6>
                        <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
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

                    <form action="{{ route('academic-levels.update', $academicLevel->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row">
                                {{-- Title (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="title" class="form-label fw-bold">{{ __('app.title') }} (AR)</label>
                                    <input type="text" name="title" id="title" 
                                        class="form-control" placeholder="{{ __('app.title') }} (AR)"
                                        value="{{ old('title', $academicLevel->title) }}">
                                </div>

                                {{-- Title (English) --}}
                                <div class="col-md-6">
                                    <label for="title_en" class="form-label fw-bold">{{ __('app.title') }} (EN)</label>
                                    <input type="text" name="title_en" id="title_en" 
                                        class="form-control" placeholder="{{ __('app.title') }} (EN)"
                                        value="{{ old('title_en', $academicLevel->title_en) }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <span class="form-label fw-bold d-block mb-2">{{ __('app.quote icon') }}</span>
                                    <div class="d-flex flex-wrap gap-3 align-items-center">
                                        @foreach (\App\Models\AcademicLevel::QUOTE_ICON_COLORS as $color)
                                            <div class="form-check form-check-inline m-0">
                                                <input class="form-check-input" type="radio" name="quote_icon_color" id="quote_icon_color_{{ $loop->index }}" value="{{ $color }}"
                                                    {{ old('quote_icon_color', $academicLevel->quote_icon_color ?? \App\Models\AcademicLevel::DEFAULT_QUOTE_ICON_COLOR) === $color ? 'checked' : '' }}>
                                                <label class="form-check-label d-inline-flex align-items-center gap-2" for="quote_icon_color_{{ $loop->index }}">
                                                    <span class="rounded-circle border d-inline-block" style="width:28px;height:28px;background-color:{{ $color }};" title="{{ $color }}"></span>
                                                    <span class="small text-muted">{{ $color }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="row mt-3">
                                {{-- Description (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="description" class="form-label fw-bold">{{ __('app.description') }} (AR)</label>
                                    <textarea name="description" id="description" placeholder="{{ __('app.description') }} (AR)" rows="3" class="form-control">{{ old('description', $academicLevel->description) }}</textarea>
                                </div>

                                {{-- Description (English) --}}
                                <div class="col-md-6">
                                    <label for="description_en" class="form-label fw-bold">{{ __('app.description') }} (EN)</label>
                                    <textarea name="description_en" id="description_en" placeholder="{{ __('app.description') }} (EN)" rows="3" class="form-control">{{ old('description_en', $academicLevel->description_en) }}</textarea>
                                </div>
                            </div> -->
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="image">{{ __('app.image') }} - {{ __('app.size')}} (318x318px)</label>
                                    <input type="file" id="image" name="image" data-default-file="{{ $academicLevel->image }}"  data-plugins="dropify" data-height="150" data-allowed-file-extensions="png jpg jpeg webp" />
                                    <small class="mt-2 d-block">Allow (jpg, jpeg, png, webp)</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label for="thumbnail_male">{{ __('app.thumbnail_male') }} - {{ __('app.size')}} (318x318px)</label>
                                    <input type="file" id="thumbnail_male" name="thumbnail_male" data-default-file="{{ $academicLevel->thumbnail_male }}" data-plugins="dropify" data-height="150" data-allowed-file-extensions="png jpg jpeg webp" />
                                    <small class="mt-2 d-block">Allow (jpg, jpeg, png, webp)</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="thumbnail_female">{{ __('app.thumbnail_female') }} - {{ __('app.size')}} (318x318px)</label>
                                    <input type="file" id="thumbnail_female" name="thumbnail_female" data-default-file="{{ $academicLevel->thumbnail_female }}" data-plugins="dropify" data-height="150" data-allowed-file-extensions="png jpg jpeg webp" />
                                    <small class="mt-2 d-block">Allow (jpg, jpeg, png, webp)</small>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('academic-levels.index') }}" class="btn btn-secondary me-2">
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
