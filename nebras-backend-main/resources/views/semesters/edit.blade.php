@extends('layouts.app')
@section('title'){{ __('app.semesters') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">
                            {{ __('app.semesters') }} - {{ __('app.edit')." #".$semester->id }}
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

                    <form action="{{ route('semesters.update', $semester->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row">
                                {{-- Title (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="title" class="form-label fw-bold">{{ __('app.title') }} (AR)</label>
                                    <input type="text" name="title" id="title" 
                                        class="form-control" placeholder="{{ __('app.title') }} (AR)"
                                        value="{{ old('title', $semester->title) }}">
                                </div>

                                {{-- Title (English) --}}
                                <div class="col-md-6">
                                    <label for="title_en" class="form-label fw-bold">{{ __('app.title') }} (EN)</label>
                                    <input type="text" name="title_en" id="title_en" 
                                        class="form-control" placeholder="{{ __('app.title') }} (EN)"
                                        value="{{ old('title_en', $semester->title_en) }}">
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Type --}}
                                <div class="col-md-6">
                                    <label for="type" class="form-label fw-bold">{{ __('app.semester type') }}</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="1" {{ old('type', $semester->type) == 1 ? 'selected' : '' }}>
                                            {{ __('app.semester one') }}
                                        </option>
                                        <option value="2" {{ old('type', $semester->type) == 2 ? 'selected' : '' }}>
                                            {{ __('app.semester two') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('semesters.index') }}" class="btn btn-secondary me-2">
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
