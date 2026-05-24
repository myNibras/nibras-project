@extends('layouts.app')
@section('title'){{ __('app.coupons') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.coupons') }} - {{ __('app.edit')." #".$coupon->id }}</h6>
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

                    <form action="{{ route('coupons.update', $coupon->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row">
                                {{-- Coupon Code --}}
                                <div class="col-md-6">
                                    <label for="coupon_code" class="form-label fw-bold">{{ __('app.coupon code') }}</label>
                                    <div class="input-group">
                                        <input type="text" name="coupon_code" id="coupon_code" 
                                            class="form-control" 
                                            value="{{ old('coupon_code', $coupon->coupon_code) }}" 
                                            placeholder="{{ __('app.coupon code') }}" readonly>
                                        <button type="button" class="btn btn-outline-primary" id="generate-code-btn" disabled>
                                            <i class="fa-solid fa-shuffle"></i> {{ __('app.generate') }}
                                        </button>
                                    </div>
                                    <small class="text-muted">{{ __('app.coupon code cannot be changed') }}</small>
                                </div>

                                {{-- Discount Percentage --}}
                                <div class="col-md-6">
                                    <label for="discount_percentage" class="form-label fw-bold">{{ __('app.discount percentage') }} (%) <span class="text-danger">*</span></label>
                                    <input type="number" name="discount_percentage" id="discount_percentage" 
                                        class="form-control" 
                                        value="{{ old('discount_percentage', $coupon->discount_percentage) }}" 
                                        min="0.01" max="100" step="0.01" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Expiry Date --}}
                                <div class="col-md-6">
                                    <label for="expiry_date" class="form-label fw-bold">{{ __('app.expiry date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="expiry_date" id="expiry_date" 
                                        class="form-control" 
                                        value="{{ old('expiry_date', $coupon->expiry_date->format('Y-m-d')) }}" required>
                                </div>

                                {{-- Student (Display Only) --}}
                                @if(($coupon->type ?? 'general') !== 'gift')
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.student') }}</label>
                                    <div class="form-control-plaintext">
                                        @if($coupon->student)
                                            {{ $coupon->student->name }} ({{ $coupon->student->email }})
                                        @else
                                            <span class="text-muted">{{ __('app.all students') }}</span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="row mt-3">
                                {{-- Number of Coupons (Display Only) --}}
                                @if(($coupon->type ?? 'general') !== 'gift' && $coupon->student_id)
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.number of coupons') }}</label>
                                    <div class="form-control-plaintext">{{ $coupon->number_of_coupons ?? 0 }}</div>
                                </div>
                                @endif

                                {{-- Limit Usage --}}
                                <div class="col-md-6">
                                    <label for="limit_usage" class="form-label fw-bold">{{ __('app.limit usage') }}</label>
                                    <input type="number" name="limit_usage" id="limit_usage" 
                                        class="form-control" 
                                        value="{{ old('limit_usage', $coupon->limit_usage ?? 1) }}" 
                                        min="0" step="1" readonly>
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.status') }}</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" 
                                            name="status" id="status" value="1" 
                                            {{ old('status', $coupon->status) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">
                                            {{ __('app.active') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('coupons.index') }}" class="btn btn-secondary me-2">
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
        // No JavaScript needed since all fields are readonly except discount_percentage, expiry_date, and status

    });
</script>
@endpush

