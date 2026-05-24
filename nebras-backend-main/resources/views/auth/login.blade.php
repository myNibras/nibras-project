@extends('layouts.guest')
@section('title'){{ __('app.login') }}@endsection

@section('content')
<style>
    .nibras-login {
        min-height: 100vh;
        background: linear-gradient(135deg, #F1F9FF 0%, #E6F1FB 50%, #DDEBFA 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        position: relative;
        overflow: hidden;
    }
    .nibras-login::before,
    .nibras-login::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        pointer-events: none;
    }
    .nibras-login::before {
        width: 420px; height: 420px;
        background: rgba(19, 150, 253, 0.20);
        top: -120px; left: -120px;
    }
    .nibras-login::after {
        width: 380px; height: 380px;
        background: rgba(253, 154, 51, 0.18);
        bottom: -120px; right: -120px;
    }

    .nibras-card {
        position: relative;
        z-index: 1;
        max-width: 1080px;
        width: 100%;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(4, 52, 88, 0.18), 0 12px 30px rgba(15, 23, 42, 0.06);
        background: #fff;
    }

    .nibras-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        min-height: 600px;
    }
    @media (max-width: 991px) {
        .nibras-grid { grid-template-columns: 1fr; }
        .nibras-brand { display: none; }
    }

    .nibras-brand {
        position: relative;
        background: linear-gradient(160deg, #043458 0%, #09528A 50%, #1396FD 110%);
        color: #fff;
        padding: 3.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }
    .nibras-brand::after {
        content: '';
        position: absolute;
        right: -120px; bottom: -120px;
        width: 360px; height: 360px;
        background: radial-gradient(closest-side, rgba(255,255,255,0.16), transparent 70%);
        pointer-events: none;
    }
    [dir='rtl'] .nibras-brand::after { right: auto; left: -120px; }

    .nibras-brand-logo {
        width: 96px; height: 96px;
        border-radius: 20px;
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(8px);
        padding: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
    }
    .nibras-brand-logo img { max-width: 100%; max-height: 100%; }

    .nibras-headline {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: 1rem;
    }
    .nibras-subline {
        font-size: 1.05rem;
        opacity: 0.85;
        max-width: 360px;
        line-height: 1.7;
    }
    .nibras-quote {
        position: relative;
        z-index: 1;
        margin-top: auto;
        padding: 1.25rem 1.5rem;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 16px;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    .nibras-quote span {
        display: block;
        font-weight: 700;
        opacity: 0.95;
    }
    .nibras-quote span + span {
        font-weight: 400;
        opacity: 0.75;
        margin-top: 4px;
    }

    .nibras-form-pane {
        padding: 3rem 2.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    @media (max-width: 575px) {
        .nibras-form-pane { padding: 2rem 1.25rem; }
    }

    .nibras-form-title {
        color: #043458;
        font-size: 1.75rem;
        font-weight: 800;
        margin-bottom: 0.35rem;
    }
    .nibras-form-subtitle {
        color: #64748B;
        font-size: 0.95rem;
        margin-bottom: 2rem;
    }

    .nibras-form-group { margin-bottom: 1.25rem; }
    .nibras-form-label {
        display: block;
        color: #043458;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
    }

    .nibras-input-wrap { position: relative; }
    .nibras-input-wrap > i {
        position: absolute;
        top: 50%; transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.95rem;
    }
    [dir='ltr'] .nibras-input-wrap > i { left: 16px; }
    [dir='rtl'] .nibras-input-wrap > i { right: 16px; }

    .nibras-input {
        width: 100%;
        height: 48px;
        border: 1.5px solid #D7DEE4;
        border-radius: 14px;
        background: #fff;
        font-size: 1rem;
        color: #0f172a;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    [dir='ltr'] .nibras-input { padding: 0 16px 0 44px; }
    [dir='rtl'] .nibras-input { padding: 0 44px 0 16px; }
    .nibras-input::placeholder { color: #94a3b8; }
    .nibras-input:focus {
        border-color: #1396FD;
        box-shadow: 0 0 0 4px rgba(19,150,253,0.12);
    }

    .nibras-password-toggle {
        position: absolute;
        top: 50%; transform: translateY(-50%);
        background: transparent; border: 0;
        color: #64748b; cursor: pointer; padding: 4px;
    }
    [dir='ltr'] .nibras-password-toggle { right: 12px; }
    [dir='rtl'] .nibras-password-toggle { left: 12px; }

    .nibras-helper-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: -0.25rem;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
    }
    .nibras-checkbox {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        cursor: pointer;
        user-select: none;
    }
    .nibras-checkbox input { accent-color: #1396FD; width: 16px; height: 16px; }

    .nibras-submit {
        width: 100%;
        height: 50px;
        border-radius: 14px;
        border: 0;
        background: linear-gradient(135deg, #043458 0%, #09528A 100%);
        color: #fff;
        font-weight: 700;
        font-size: 1rem;
        letter-spacing: 0.2px;
        cursor: pointer;
        transition: transform .12s ease, box-shadow .15s ease, opacity .15s ease;
        box-shadow: 0 12px 28px rgba(4, 52, 88, 0.28);
    }
    .nibras-submit:hover { transform: translateY(-1px); box-shadow: 0 16px 32px rgba(4, 52, 88, 0.34); }
    .nibras-submit:active { transform: translateY(0); }
    .nibras-submit:disabled { opacity: 0.7; cursor: not-allowed; }

    .nibras-error {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        color: #B91C1C;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-size: 0.875rem;
        margin-bottom: 1.25rem;
        display: flex; align-items: flex-start; gap: 8px;
    }
    .nibras-error i { color: #DC2626; margin-top: 2px; }

    .nibras-field-error {
        color: #B91C1C;
        font-size: 0.8rem;
        margin-top: 6px;
    }
</style>

<div class="nibras-login">
    <div class="nibras-card">
        <div class="nibras-grid">

            <!-- Brand pane (hidden on mobile) -->
            <aside class="nibras-brand">
                <div>
                    <div class="nibras-brand-logo">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Nibras" />
                    </div>
                    <h1 class="nibras-headline">{{ __('app.welcome back') }}</h1>
                    <p class="nibras-subline">{{ __('app.your dashboard') }}</p>
                </div>

                <div class="nibras-quote">
                    <span>{{ __('app.trusted by parents') }}</span>
                    <span>{{ __('app.loved by students') }}</span>
                </div>
            </aside>

            <!-- Form pane -->
            <section class="nibras-form-pane">
                <h2 class="nibras-form-title">{{ __('app.admin login') }}</h2>
                <p class="nibras-form-subtitle">{{ __('app.sign in subtitle') }}</p>

                @if ($errors->any())
                    <div class="nibras-error" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="nibras-form-group">
                        <label class="nibras-form-label" for="email">{{ __('app.email') }}</label>
                        <div class="nibras-input-wrap">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="email" id="email"
                                class="nibras-input"
                                value="{{ old('email') }}"
                                placeholder="{{ __('app.enter your email') }}"
                                required autofocus autocomplete="email" />
                        </div>
                        @error('email')<div class="nibras-field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="nibras-form-group">
                        <label class="nibras-form-label" for="password">{{ __('app.password') }}</label>
                        <div class="nibras-input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="password" id="password"
                                class="nibras-input"
                                placeholder="{{ __('app.enter your password') }}"
                                required autocomplete="current-password" />
                            <button type="button" class="nibras-password-toggle"
                                onclick="(function(b){var i=b.parentElement.querySelector('input');var icon=b.querySelector('i');var hidden=i.type==='password';i.type=hidden?'text':'password';icon.className=hidden?'fa-solid fa-eye-slash':'fa-solid fa-eye';})(this)"
                                aria-label="Toggle password visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        @error('password')<div class="nibras-field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="nibras-helper-row">
                        <label class="nibras-checkbox">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />
                            <span>{{ __('app.remember me') }}</span>
                        </label>
                    </div>

                    <button type="submit" class="nibras-submit">
                        <i class="fa-solid fa-arrow-right-to-bracket me-2 ms-2"></i>
                        {{ __('app.login') }}
                    </button>
                </form>
            </section>

        </div>
    </div>
</div>
@endsection
