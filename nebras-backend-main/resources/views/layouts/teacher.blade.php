<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ (app()->getLocale() == 'en') ? 'ltr' : 'rtl' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - {{ __('app.teachers') }}</title>
    <!-- Fonts and icons -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" />
    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/dc39a05fe4.js" crossorigin="anonymous"></script>
    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- Toastr -->
    <link href="{{ asset('assets/css/toastr.min.css') }}" rel="stylesheet"/>
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- CSS Files -->
    <link href="{{ asset('assets/css/material-dashboard.css?v=3.4.0') }}" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" />
</head>

<body class="g-sidenav-show {{ (app()->getLocale() == 'en') ? 'ltr' : 'rtl' }} bg-gray-100">
    @include('teacher.partials.sidebar')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg overflow-x-hidden">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl position-sticky blur shadow-blur mt-4 left-auto top-1 z-index-sticky" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-3 justify-content-between gap-2 align-items-center">
                <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                    <div class="sidenav-toggler-inner">
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                        <i class="sidenav-toggler-line"></i>
                    </div>
                </a>
                <div class="d-flex align-items-center gap-3">
                    @include('teacher.partials.chat-notifications-bell')
                    <div>
                        <i class="fa-solid fa-earth-americas" style="margin-bottom: 3px;"></i>
                        @if(app()->getLocale() == 'ar')
                            <a href="{{ LaravelLocalization::getLocalizedURL('en') }}" class="notify-item text-right">
                                English
                            </a>
                        @else
                            <a href="{{ LaravelLocalization::getLocalizedURL('ar') }}" class="notify-item text-left">
                                العربية
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->

        @if(session('impersonate.admin_id'))
        <div class="alert alert-warning alert-dismissible fade show mx-3 mt-2 mb-0 rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2" role="alert">
            <span class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-user-secret"></i>
                <strong>{{ __('app.viewing as teacher') }}</strong>
                {{ __('app.you are viewing the teacher panel as admin') }}
            </span>
            <a href="{{ route('teacher.leave-impersonation') }}" class="btn btn-sm btn-dark">
                <i class="fa-solid fa-arrow-left me-1"></i>{{ __('app.back to admin') }}
            </a>
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Core JS Files -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/material-dashboard.js?v=3.4.0') }}"></script>
    <script src="{{ asset('assets/js/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/js/chat-notifications.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/ar.js"></script>

    @stack('scripts')
</body>

</html>

