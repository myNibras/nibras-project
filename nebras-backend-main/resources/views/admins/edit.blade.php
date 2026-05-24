@extends('layouts.app')
@section('title'){{ __('app.admins') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.admins') }} - {{ __('app.edit')." #".$admin->id }}</h6>
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

                    <form action="{{ route('admins.update', $admin->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row">
                                {{-- Name --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">{{ __('app.name') }}</label>
                                    <input type="text" name="name" id="name" 
                                        class="form-control" 
                                        value="{{ old('name', $admin->name) }}">
                                </div>

                                {{-- Email --}}
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-bold">{{ __('app.email') }}</label>
                                    <input type="email" name="email" id="email" 
                                        class="form-control" 
                                        value="{{ old('email', $admin->email) }}">
                                </div>
                            </div>

                            <div class="row mt-3">

                                {{-- Phone --}}
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-bold">{{ __('app.phone number') }}</label>
                                    <input type="text" name="phone" id="phone" 
                                        class="form-control" value="{{ old('phone', $admin->phone) }}">
                                </div>

                                {{-- Role --}}
                                <div class="col-md-6">
                                    <label for="role" class="form-label fw-bold">{{ __('app.role') }}</label>
                                    <select name="role" id="role" class="form-control">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name_en }}" {{ $adminRole == $role->name_en ? 'selected' : '' }}>
                                                {{ $role->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                {{-- Password --}}
                                <div class="col-md-6 form-group position-relative">
                                    <label for="password" class="form-label fw-bold">{{ __('app.password') }}</label>
                                    <input type="password" name="password" id="password" 
                                        placeholder="{{ __('app.password') }}" class="form-control">
                                    <span class="toggle-password" onclick="togglePassword('password', this)" 
                                        style="position:absolute; top:38px; right:20px; cursor:pointer;">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </div>

                                {{-- Confirm Password --}}
                                <div class="col-md-6 form-group position-relative">
                                    <label for="password_confirmation" class="form-label fw-bold">{{ __('app.confirm_password') }}</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" 
                                        placeholder="{{ __('app.confirm_password') }}" class="form-control">
                                    <span class="toggle-password" onclick="togglePassword('password_confirmation', this)" 
                                        style="position:absolute; top:38px; right:20px; cursor:pointer;">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('admins.index') }}" class="btn btn-secondary me-2">
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
