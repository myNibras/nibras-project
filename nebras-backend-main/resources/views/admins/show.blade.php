@extends('layouts.app')
@section('title'){{ __('app.admins') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.admins') }} - #{{ $admin->id }}</h6>
                </div>
                <div class="card-body">
                    
                    {{-- Name --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.name') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">{{ $admin->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.email') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">{{ $admin->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.phone number') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">{{ $admin->phone }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="col-sm-4 col-form-label fw-bold">{{ __('app.role') }}</label>
                            <div class="col-sm-8">
                                <p class="form-control-plaintext">{{ $adminRole }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.created at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $admin->created_at ? $admin->created_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.updated at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $admin->updated_at ? $admin->updated_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.status') }}</label>
                            <div>
                                <p class="form-control-plaintext">
                                    @if($admin->status)
                                        <span class="badge bg-success">{{ __('app.active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('app.inactive') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('admins.index') }}" class="btn btn-secondary me-2">{{ __('app.back') }}</a>
                        <a href="{{ route('admins.edit', $admin->id) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
