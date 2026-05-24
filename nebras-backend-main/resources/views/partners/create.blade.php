@extends('layouts.app')
@section('title'){{ __('app.partners') }} - {{ __('app.add new') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.partners') }} - {{ __('app.add new') }}</h6>
                        <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">

                    <form action="{{ route('partners.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row mb-3">
                                {{-- Name AR --}}
                                <div class="col-md-6">
                                    <label for="name_ar" class="form-label fw-bold">{{ __('app.name_ar') }}</label>
                                    <input type="text" name="name_ar" id="name_ar" placeholder="{{ __('app.name_ar') }}"
                                        class="form-control" value="{{ old('name_ar') }}">
                                    @error('name_ar')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Name EN --}}
                                <div class="col-md-6">
                                    <label for="name_en" class="form-label fw-bold">{{ __('app.name_en') }}</label>
                                    <input type="text" name="name_en" id="name_en" placeholder="{{ __('app.name_en') }}"
                                        class="form-control" value="{{ old('name_en') }}">
                                    @error('name_en')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Logo --}}
                                <div class="col-md-12">
                                    <label for="logo">{{ __('app.logo') }} <span class="text-danger">*</span> ({{ __('app.extension') }}: jpg, jpeg, png, webp — {{ __('app.max size') }}: 10MB)</label>
                                    <input type="file" id="logo" name="logo" data-plugins="dropify" data-height="150" data-allowed-file-extensions="png jpg jpeg webp" required />
                                    <small class="mt-2 d-block">Allow (jpg, jpeg, png, webp)</small>
                                    @error('logo')
                                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Status --}}
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" 
                                            name="status" 
                                            type="checkbox" 
                                            value="1" 
                                            role="switch" 
                                            id="status"
                                            {{ old('status') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">{{ __('app.status') }}</label>
                                    </div>
                                    @error('status')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3 mb-3">
                            <a href="{{ route('partners.index') }}" class="btn btn-secondary me-2">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('app.save') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

