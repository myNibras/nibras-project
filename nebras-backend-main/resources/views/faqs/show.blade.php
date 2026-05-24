@extends('layouts.app')
@section('title'){{ __('app.faqs') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.faqs') }} - #{{ $faq->id }}</h6>
                </div>
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="col-form-label fw-bold">{{ __('app.question') }} (AR)</label>
                            <p class="form-control-plaintext">{{ $faq->question }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="col-form-label fw-bold">{{ __('app.question') }} (EN)</label>
                            <p class="form-control-plaintext">{{ $faq->question_en }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="col-form-label fw-bold">{{ __('app.answer') }} (AR)</label>
                            <div class="form-control-plaintext" style="white-space: pre-wrap;">{{ $faq->answer }}</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="col-form-label fw-bold">{{ __('app.answer') }} (EN)</label>
                            <div class="form-control-plaintext" style="white-space: pre-wrap;">{{ $faq->answer_en }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-form-label fw-bold">{{ __('app.status') }}</label>
                            <div>
                                @if($faq->status)
                                    <span class="badge bg-success">{{ __('app.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="col-form-label fw-bold">{{ __('app.created at') }}</label>
                            <p class="form-control-plaintext">{{ $faq->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('faqs.index') }}" class="btn btn-secondary">
                            {{ __('app.back') }}
                        </a>
                        <a href="{{ route('faqs.edit', $faq) }}" class="btn btn-primary">
                            {{ __('app.edit') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
