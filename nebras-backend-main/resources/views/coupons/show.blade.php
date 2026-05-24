@extends('layouts.app')
@section('title'){{ __('app.coupons') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.coupons') }} - #{{ $coupon->id }}</h6>
                </div>
                <div class="card-body">
                    
                    {{-- Coupon Code --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.coupon code') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext"><strong>{{ $coupon->coupon_code }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.discount percentage') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">{{ $coupon->discount_percentage }}%</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.expiry date') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">{{ $coupon->expiry_date->format('Y-m-d') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.student') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">
                                    @if($coupon->student)
                                        {{ $coupon->student->name }} ({{ $coupon->student->email }})
                                    @else
                                        <span class="text-muted">{{ __('app.optional') }} - {{ __('app.all students') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($coupon->student)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.number of coupons') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">{{ $coupon->number_of_coupons ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.limit usage') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">{{ $coupon->limit_usage ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.type') }}</label>
                            <div class="col-sm-8">
                                @if(($coupon->type ?? 'general') === 'owner')
                                    <span class="badge bg-primary">{{ __('app.owner') }}</span>
                                @elseif(($coupon->type ?? 'general') === 'gift')
                                    <span class="badge bg-info">{{ __('app.gift') }}</span>
                                @else
                                    <span class="badge bg-success">{{ __('app.general') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.status') }}</label>
                            <div class="col-sm-8">
                                @if($coupon->expiry_date < now()->toDateString())
                                    <span class="badge bg-danger">{{ __('app.expired') }}</span>
                                @elseif($coupon->status)
                                    <span class="badge bg-success">{{ __('app.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.created at') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">{{ $coupon->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.updated at') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">{{ $coupon->updated_at->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('coupons.index') }}" class="btn btn-secondary">
                            {{ __('app.back') }}
                        </a>
                        <a href="{{ route('coupons.edit', $coupon->id) }}" class="btn btn-primary">
                            {{ __('app.edit') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

