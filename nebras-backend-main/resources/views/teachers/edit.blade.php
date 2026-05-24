@extends('layouts.app')
@section('title'){{ __('app.teachers') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">
                            {{ __('app.teachers') }} - {{ __('app.edit')." #".$teacher->id }}
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

                    <form action="{{ route('teachers.update', $teacher->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row">
                                {{-- Name (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">{{ __('app.name') }} (AR)</label>
                                    <input type="text" name="name" id="name" 
                                        class="form-control" placeholder="{{ __('app.name') }} (AR)"
                                        value="{{ old('name', $teacher->name) }}" maxlength="50">
                                </div>

                                {{-- Name (English) --}}
                                <div class="col-md-6">
                                    <label for="name_en" class="form-label fw-bold">{{ __('app.name') }} (EN)</label>
                                    <input type="text" name="name_en" id="name_en" 
                                        class="form-control" placeholder="{{ __('app.name') }} (EN)"
                                        value="{{ old('name_en', $teacher->name_en) }}" maxlength="50">
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Email --}}
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-bold">{{ __('app.email') }}</label>
                                    <input type="email" name="email" id="email"
                                           class="form-control" placeholder="{{ __('app.email') }}"
                                           value="{{ old('email', $teacher->email) }}" maxlength="255">
                                </div>

                                {{-- Password (leave blank to keep current) --}}
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-bold">
                                        {{ __('app.password') }}
                                        <small class="text-muted">({{ __('app.leave blank to keep current') }})</small>
                                    </label>
                                    <input type="password" name="password" id="password"
                                           class="form-control" placeholder="{{ __('app.password') }}"
                                           minlength="8">
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Position --}}
                                <div class="col-md-6">
                                    <label for="position_id" class="form-label fw-bold">{{ __('app.position') }}</label>
                                    <select name="position_id" id="position_id" class="form-select">
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id', $teacher->position_id) == $position->id ? 'selected' : '' }}>
                                                {{ $position->getLocalizationName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Years of experience --}}
                                <div class="col-md-6">
                                    <label for="years_of_experience" class="form-label fw-bold">{{ __('app.years of experience') }}</label>
                                    <input type="number" name="years_of_experience" id="years_of_experience" min="0" step="1"
                                        class="form-control" value="{{ old('years_of_experience', $teacher->years_of_experience ?? 0) }}" placeholder="0">
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Description (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="description" class="form-label fw-bold">{{ __('app.description') }} (AR)</label>
                                    <textarea name="description" id="description" class="form-control" rows="4" maxlength="5000" placeholder="{{ __('app.description') }} (AR)">{{ old('description', $teacher->description) }}</textarea>
                                </div>
                                {{-- Description (English) --}}
                                <div class="col-md-6">
                                    <label for="description_en" class="form-label fw-bold">{{ __('app.description') }} (EN)</label>
                                    <textarea name="description_en" id="description_en" class="form-control" rows="4" maxlength="5000" placeholder="{{ __('app.description') }} (EN)">{{ old('description_en', $teacher->description_en) }}</textarea>
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Image --}}
                                <div class="col-md-12">
                                    <label for="image" class="form-label fw-bold">{{ __('app.image') }}</label>
                                    <input type="file" id="image" name="image"
                                        data-plugins="dropify"
                                        data-height="150"
                                        data-default-file="{{ $teacher->image }}"
                                        data-allowed-file-extensions="png jpg jpeg webp" />
                                    <small class="mt-2 d-block">
                                        {{ __('app.size') }} (275x325px) — Allow (jpg, jpeg, png, webp) — Max 10MB
                                    </small>
                                    <input type="hidden" name="remove_image" id="remove_image" value="0">
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Video --}}
                                <div class="col-md-12">
                                    <label for="video" class="form-label fw-bold">{{ __('app.video') }}</label>
                                    @if($teacher->video)
                                        <div class="mb-2">
                                            <video src="{{ $teacher->video }}" controls style="max-width: 100%; max-height: 200px;"></video>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="remove_video" id="remove_video" value="1">
                                                <label class="form-check-label" for="remove_video">{{ __('app.remove video') }}</label>
                                            </div>
                                        </div>
                                    @endif
                                    <input type="file" id="video" name="video" class="form-control" accept="video/mp4,video/quicktime,video/webm">
                                    <small class="mt-2 d-block">MP4, MOV, WEBM - Max 50MB. {{ $teacher->video ? __('app.upload new to replace') : '' }}</small>
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Stats (read-only) --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('app.reviews') }}</label>
                                    <input type="text" class="form-control" value="{{ $stats['reviews'] ?? 0 }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('app.number of classes') }}</label>
                                    <input type="text" class="form-control" value="{{ $stats['classes_count'] ?? 0 }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('app.number of students') }}</label>
                                    <input type="text" class="form-control" value="{{ $stats['students_count'] ?? 0 }}" readonly>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" 
                                            name="status" 
                                            type="checkbox" 
                                            value="1" 
                                            role="switch" 
                                            id="status"
                                           {{ old('status', $teacher->status ?? 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">{{ __('app.status') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('teachers.index') }}" class="btn btn-secondary me-2">
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

@push('scripts')
<script>
    $(document).ready(function() {
        var drEvent = $('#image').dropify();

        drEvent.on('dropify.afterClear', function(event, element){
            $('#remove_image').val('1');
        });

        drEvent.on('dropify.errors', function(event, element){
            $('#remove_image').val('0');
        });
    });
</script>
@endpush
