@extends('layouts.app')
@section('title'){{ __('app.settings') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize mb-0">{{ __('app.settings') }}</h6>
                        <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-reply"></i>
                            <span style="height:16px;">{{ __('app.back') }}</span>
                        </button>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="{{ route('settings.update-all') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-8">
                                @forelse($settings as $setting)
                                <div class="d-flex align-items-center justify-content-between py-3 border-bottom border-gray-200">
                                    <label class="form-label mb-0 fw-bold" for="setting-{{ $setting->id }}">
                                        {{ $setting->getLocalizationLabel() }}
                                    </label>
                                    <div class="form-check form-switch mb-0">
                                        <input type="hidden" name="settings[{{ $setting->id }}]" value="0">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            role="switch"
                                            name="settings[{{ $setting->id }}]"
                                            id="setting-{{ $setting->id }}"
                                            value="1"
                                            {{ $setting->value ? 'checked' : '' }}
                                        >
                                    </div>
                                </div>
                                @empty
                                <p class="text-muted mb-0">{{ __('app.no results found') }}</p>
                                @endforelse
                            </div>
                        </div>
                        @if($settings->isNotEmpty())
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save me-1"></i>{{ __('app.save') }}
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
