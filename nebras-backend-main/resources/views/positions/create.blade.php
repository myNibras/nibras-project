@extends('layouts.app')
@section('title'){{ __('app.positions') }} - {{ __('app.add new') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize mb-0">{{ __('app.positions') }} - {{ __('app.add new') }}</h6>
                        <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">

                    @if ($errors->any())
                        <div class="alert alert-danger mx-4">
                            <ul class="mb-0 text-white">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('positions.store') }}" method="POST">
                        @csrf
                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">{{ __('app.name') }} (AR)</label>
                                    <input type="text" name="name" id="name" placeholder="{{ __('app.name') }} (AR)"
                                        class="form-control" value="{{ old('name') }}" maxlength="50">
                                </div>
                                <div class="col-md-6">
                                    <label for="name_en" class="form-label fw-bold">{{ __('app.name') }} (EN)</label>
                                    <input type="text" name="name_en" id="name_en" placeholder="{{ __('app.name') }} (EN)"
                                        class="form-control" value="{{ old('name_en') }}" maxlength="50">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">{{ __('app.status') }} ({{ __('app.active') }}/{{ __('app.inactive') }})</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3">
                            <a href="{{ route('positions.index') }}" class="btn btn-secondary me-2">{{ __('app.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
