<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ (app()->getLocale() == 'en') ? 'ltr' : 'rtl' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <!--     Fonts and icons     -->
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
    <!-- Datatable -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- CSS Files -->
    <link href="{{ asset('assets/css/material-dashboard.css?v=3.2.5') }}" rel="stylesheet" />
    <!-- Dropify -->
    <link href="{{ asset('assets/css/dropify.min.css') }}" rel="stylesheet" />
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" />
</head>

<body class="g-sidenav-show {{ (app()->getLocale() == 'en') ? 'ltr' : 'rtl' }} bg-gray-100">

  @include('components.sidebar')
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
    </nav>
    <!-- End Navbar -->
    @yield('content')
  </main>
  
    <!--   Core JS Files   -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/material-dashboard.js?v=3.4.0') }}"></script>
    <script src="{{ asset('assets/js/dropify.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/toastr.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/ar.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/main.js?v=1.1') }}"></script>
    
    @stack('scripts')

    <script>
        $(document).on('click', '.update-status', function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            var table = $(this).data("table");
            var message = $(this).data("message");
            var url = '';
            if (table) {
                url = '/{{ app()->getLocale() }}/' + table + '/change-status/' + id;
            }

            Swal.fire({
                title: "",
                text: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __("app.yes") }}',
                cancelButtonText: '{{ __("app.cancel") }}'
            })
                .then((result) => {
                    if (result.value) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: {},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {                                
                                if (response.status == "success") {
                                    handleTable();
                                } else {
                                    Swal.fire({
                                        title: "Error",
                                        text: "{{ __('app.something went wrong') }}",
                                        icon: "error",
                                        confirmButtonText: "{{ __('app.ok') }}"
                                    }).then(() => {
                                        handleTable();
                                    });
                                }

                            },
                            error: function (err) {
                                Swal.fire({
                                    title: "Error",
                                    text: err.responseJSON.message,
                                    icon: "error",
                                    confirmButtonText: "{{ __('app.ok') }}"
                                }).then(() => {
                                    handleTable();
                                });
                            }
                        });
                    }
                });

        });
        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            var table = $(this).data("table");
            var url = '/{{ app()->getLocale() }}/' + table + '/' + id;

            Swal.fire({
                title: "",
                text: "{{ __('app.are you sure you want delete this record!') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __("app.yes, delete it!") }}',
                cancelButtonText: '{{ __("app.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) { // <- use isConfirmed
                    $.ajax({
                        type: "DELETE",
                        url: url,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire({
                                title: "",
                                text: "{{ __('app.deleted successfully') }}",
                                icon: "success",
                                confirmButtonText: "{{ __('app.ok') }}"
                            }).then(() => {
                                handleTable();
                            });
                        },
                        error: function (err) {
                            Swal.fire({
                                title: "Error",
                                text: err.responseJSON.message,
                                icon: "error",
                                confirmButtonText: "{{ __('app.ok') }}"
                            });
                        }
                    });
                }
            });
        });
    </script>
    <script>
        function refreshToken(){
            $.get('/refresh-csrf').done(function(data){
                $("[name=_token]").val(data); // the new token
            });
        }

        setInterval(refreshToken, 300000);
    </script>
    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        @if(session('success'))
            <div class="toast align-items-center text-bg-success border-0 show mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body text-white">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif

        <!-- @if(session('error'))
            <div class="toast align-items-center text-bg-danger border-0 show mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="toast align-items-center text-bg-danger border-0 show mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ $errors->first() }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif -->
    </div>

</body>

</html>