@extends('layouts.app')
@section('title'){{ __('app.students') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.students') }} - {{ __('app.add new') }}</h6>
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

                    <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row">
                                {{-- Name --}}
                                <div class="col-md-4">
                                    <label for="name" class="form-label fw-bold">{{ __('app.full name') }}</label>
                                    <input type="text" name="name" id="name" placeholder="{{ __('app.full name') }}" 
                                        class="form-control" value="{{ old('name') }}">
                                </div>

                                {{-- Email --}}
                                <div class="col-md-4">
                                    <label for="email" class="form-label fw-bold">{{ __('app.email') }}</label>
                                    <input type="email" name="email" id="email" placeholder="{{ __('app.email') }}" 
                                        class="form-control" value="{{ old('email') }}">
                                </div>

                                {{-- Phone --}}
                                <div class="col-md-4">
                                    <label for="phone" class="form-label fw-bold">{{ __('app.phone number') }}</label>
                                    <input type="text" name="phone" id="phone" placeholder="{{ __('app.phone number') }}" 
                                        class="form-control" value="{{ old('phone') }}">
                                </div>
                            </div>

                            <div class="row">
                                {{-- Age --}}
                                <div class="col-md-4">
                                    <label for="age" class="form-label fw-bold">{{ __('app.age') }}</label>
                                    <input type="number" name="age" id="age" min="1" placeholder="{{ __('app.age') }}" 
                                        class="form-control" value="{{ old('age') }}">
                                </div>

                                {{-- Gender --}}
                                <div class="col-md-4">
                                    <label for="gender" class="form-label fw-bold">{{ __('app.gender') }}</label>
                                    <select name="gender" id="gender" class="form-select ps-2">
                                        <option value="">{{ __('app.select') }}</option>
                                        <option value="0" {{ old('gender') == 0 ? 'selected' : '' }}>{{ __('app.male') }}</option>
                                        <option value="1" {{ old('gender') == 1 ? 'selected' : '' }}>{{ __('app.female') }}</option>
                                    </select>
                                </div>

                                {{-- Class --}}
                                <div class="col-md-4">
                                    <label for="class_id" class="form-label fw-bold">{{ __('app.class') }}</label>
                                    <select name="class_id" id="class_id" class="form-select ps-2">
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" 
                                                {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->getLocalizationName() }}
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

                            <div class="row">
                                {{-- Profile Picture --}}
                                <div class="col-md-12">
                                    <label for="profile_picture" class="form-label fw-bold">{{ __('app.profile picture') }}</label>
                                    <input type="file" id="profile_picture" name="profile_picture" data-plugins="dropify" data-height="150" data-allowed-file-extensions="png jpg jpeg webp" />
                                    <small class="mt-2 d-block">Allowed formats: (jpg, jpeg, png, webp) - Max size: 10MB</small>
                                </div>
                            </div>

                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('students.index') }}" class="btn btn-secondary me-2">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('app.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection