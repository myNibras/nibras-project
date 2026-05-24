<aside style="z-index:9999" class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg {{ (app()->getLocale() == 'en') ? 'fixed-start' : 'fixed-end' }} me-2 rotate-caret bg-white my-2" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute start-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand px-4 py-3 m-0" href="{{ route('teacher.dashboard') }}">
            <img src="{{ asset('assets/images/logo.png') }}" class="navbar-brand-img" alt="Logo">
        </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">
    <div class="collapse navbar-collapse px-0 w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">

            <li class="nav-item mb-1">
                <a class="nav-link text-dark {{ request()->routeIs('teacher.dashboard') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('teacher.dashboard') }}">
                    <i class="material-symbols-rounded opacity-10">dashboard</i>
                    <span class="nav-link-text mx-2">{{ __('app.dashboard') }}</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link text-dark {{ request()->routeIs('teacher.profile*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('teacher.profile') }}">
                    <i class="material-symbols-rounded opacity-10">person</i>
                    <span class="nav-link-text mx-2">{{ __('app.profile') }}</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link text-dark {{ request()->routeIs('teacher.courses*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('teacher.courses') }}">
                    <i class="material-symbols-rounded opacity-10">book_ribbon</i>
                    <span class="nav-link-text mx-2">{{ __('app.courses') }}</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link text-dark {{ request()->routeIs('teacher.notifications*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('teacher.notifications') }}">
                    <i class="material-symbols-rounded opacity-10">notifications</i>
                    <span class="nav-link-text mx-2">{{ __('app.notifications') }}</span>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a class="nav-link text-dark {{ request()->routeIs('teacher.chat*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('teacher.chat') }}">
                    <i class="material-symbols-rounded opacity-10">chat</i>
                    <span class="nav-link-text mx-2">{{ __('app.chat') }}</span>
                </a>
            </li>

        </ul>
    </div>
    <div class="sidenav-footer position-absolute w-100 bottom-0 mb-3">
        <div class="mx-3">
            <form method="POST" action="{{ route('teacher.logout') }}">
                @csrf
                <button type="submit" class="btn bg-gradient-dark w-100">{{ __('app.logout') }}</button>
            </form>
        </div>
    </div>
</aside>
