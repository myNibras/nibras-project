@extends('layouts.guest')
@section('title'){{ __('app.login') }} - {{ __('app.teachers') }}@endsection

@section('content')
<main class="main-content mt-0">
    <div class="page-header align-items-start min-vh-100">
        <span class="mask bg-gradient-dark"></span>
        <div class="container my-auto">
            <div class="row">
                <div class="col-lg-4 col-md-8 col-12 mx-auto">
                    <div class="card z-index-0 fadeIn3 fadeInBottom">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-dark shadow-dark border-radius-lg py-3 pe-1">
                                <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">
                                    {{ __('app.teachers') }} - {{ __('app.login') }}
                                </h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <form role="form" class="text-start" method="POST" action="{{ route('teacher.login') }}">
                                @csrf

                                <div class="my-3">
                                    <label class="d-block" for="email">{{ __('app.email') }}</label>
                                    <input
                                        type="email"
                                        placeholder="{{ __('app.email') }}"
                                        name="email"
                                        id="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus
                                    >
                                    @error('email')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="d-block" for="password">{{ __('app.password') }}</label>
                                    <input
                                        type="password"
                                        placeholder="{{ __('app.password') }}"
                                        name="password"
                                        id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        required
                                    >
                                    @error('password')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">
                                        {{ __('app.remember me') }}
                                    </label>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">
                                        {{ __('app.login') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

