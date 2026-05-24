@extends('layouts.app')
@section('title'){{ __('app.faqs') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.faqs') }} - {{ __('app.edit') }} #{{ $faq->id }}</h6>
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

                    <form action="{{ route('faqs.update', $faq) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row">
                                {{-- Question (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="question" class="form-label fw-bold">{{ __('app.question') }} (AR) <span class="text-danger">*</span></label>
                                    <input type="text" name="question" id="question" maxlength="100"
                                        class="form-control" value="{{ old('question', $faq->question) }}"
                                        placeholder="{{ __('app.question') }} (AR)" required>
                                    <small class="text-muted">{{ __('app.max 100 characters') }}</small>
                                </div>

                                {{-- Question (English) --}}
                                <div class="col-md-6">
                                    <label for="question_en" class="form-label fw-bold">{{ __('app.question') }} (EN) <span class="text-danger">*</span></label>
                                    <input type="text" name="question_en" id="question_en" maxlength="100"
                                        class="form-control" value="{{ old('question_en', $faq->question_en) }}"
                                        placeholder="{{ __('app.question') }} (EN)" required>
                                    <small class="text-muted">{{ __('app.max 100 characters') }}</small>
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Answer (Arabic) --}}
                                <div class="col-md-6">
                                    <label for="answer" class="form-label fw-bold">{{ __('app.answer') }} (AR) <span class="text-danger">*</span></label>
                                    <textarea name="answer" id="answer" rows="6" class="form-control"
                                        placeholder="{{ __('app.answer') }} (AR) - {{ __('app.line breaks supported') }}" required>{{ old('answer', $faq->answer) }}</textarea>
                                </div>

                                {{-- Answer (English) --}}
                                <div class="col-md-6">
                                    <label for="answer_en" class="form-label fw-bold">{{ __('app.answer') }} (EN) <span class="text-danger">*</span></label>
                                    <textarea name="answer_en" id="answer_en" rows="6" class="form-control"
                                        placeholder="{{ __('app.answer') }} (EN) - {{ __('app.line breaks supported') }}" required>{{ old('answer_en', $faq->answer_en) }}</textarea>
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.status') }}</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox"
                                            name="status" id="status" value="1"
                                            {{ old('status', $faq->status) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">
                                            {{ __('app.active') }}
                                        </label>
                                    </div>
                                </div>

                                {{-- Display order is managed via drag-and-drop in the FAQs list --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.display order') }}</label>
                                    <div class="alert alert-info mb-0 py-2 px-3 d-flex align-items-center gap-2" style="font-size: .9rem;">
                                        <i class="fa-solid fa-grip-vertical"></i>
                                        <span>{{ __('app.order is managed via drag-and-drop in the faqs list') }} (#{{ $faq->order ?? '—' }})</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3">
                            <a href="{{ route('faqs.index') }}" class="btn btn-secondary me-2">
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
