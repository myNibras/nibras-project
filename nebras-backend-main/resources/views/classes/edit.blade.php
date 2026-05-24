@extends('layouts.app')
@section('title'){{ __('app.classes') }} - {{ __('app.edit') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.classes') }} - {{ __('app.edit') }}</h6>
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

                    <form action="{{ route('classes.update', $class->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row mb-3">
                                {{-- Name (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">{{ __('app.name') }} (AR)</label>
                                    <input type="text" name="name" id="name" placeholder="{{ __('app.name') }} (AR)"
                                        class="form-control" value="{{ old('name', $class->name) }}">
                                </div>

                                {{-- Name (English) --}}
                                <div class="col-md-6">
                                    <label for="name_en" class="form-label fw-bold">{{ __('app.name') }} (EN)</label>
                                    <input type="text" name="name_en" id="name_en" placeholder="{{ __('app.name') }} (EN)"
                                        class="form-control" value="{{ old('name_en', $class->name_en) }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Academic Level --}}
                                <div class="col-md-6">
                                    <label for="academic_level_id" class="form-label fw-bold">{{ __('app.academic_level') }}</label>
                                    <select name="academic_level_id" id="academic_level_id" class="form-control form-select px-2">
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($academicLevels as $level)
                                            <option value="{{ $level->id }}" {{ old('academic_level_id', $class->academic_level_id) == $level->id ? 'selected' : '' }}>
                                                {{ $level->getLocalizationTitle() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('classes.index') }}" class="btn btn-secondary me-2">
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
