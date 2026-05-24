<aside style="z-index:9999" class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg {{ (app()->getLocale() == 'en') ? 'fixed-start' : 'fixed-end' }} me-2 rotate-caret bg-white my-2" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute start-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand px-4 py-3 m-0" href="{{ route('dashboard') }}">
        <img src="{{ asset('assets/images/logo.png') }}" class="navbar-brand-img" alt="Nibras Logo">
      </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">
    <div class="collapse navbar-collapse px-0 w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">

        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('dashboard') }}">
            <i class="material-symbols-rounded opacity-10">dashboard</i>
            <span class="nav-link-text mx-2">{{ __('app.dashboard') }}</span>
          </a>
        </li>

        @can('view home slider')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('home-sliders.index') }}">
            <i class="material-symbols-rounded opacity-10">steppers</i>
            <span class="nav-link-text mx-2">{{ __('app.home sliders') }}</span>
          </a>
        </li>
        @endcan

        @can('view news')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('news.index') }}">
            <i class="material-symbols-rounded opacity-10">newspaper</i>
            <span class="nav-link-text mx-2">{{ __('app.news') }}</span>
          </a>
        </li>
        @endcan

        @can('view article')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('article.index') }}">
            <i class="material-symbols-rounded opacity-10">article</i>
            <span class="nav-link-text mx-2">{{ __('app.article') }}</span>
          </a>
        </li>
        @endcan

        @can('view payments')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('payments.index') }}">
            <i class="material-symbols-rounded opacity-10">attach_money</i>
            <span class="nav-link-text mx-2">{{ __('app.payments') }}</span>
          </a>
        </li>
        @endcan
        @can('view installments')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('installments.index') }}">
            <i class="material-symbols-rounded opacity-10">receipt_long</i>
            <span class="nav-link-text mx-2">{{ __('app.installments') }}</span>
          </a>
        </li>
        @endcan

        @can('view coupons')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('coupons.index') }}">
            <i class="material-symbols-rounded opacity-10">local_offer</i>
            <span class="nav-link-text mx-2">{{ __('app.coupons') }}</span>
          </a>
        </li>
        @endcan

        @can('view faqs')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('faqs.index') }}">
            <i class="material-symbols-rounded opacity-10">help</i>
            <span class="nav-link-text mx-2">{{ __('app.faqs') }}</span>
          </a>
        </li>
        @endcan

        @can('view contact submissions')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('contact-submissions.index') }}">
            <i class="material-symbols-rounded opacity-10">contact_mail</i>
            <span class="nav-link-text mx-2">{{ __('app.contact submissions') }}</span>
          </a>
        </li>
        @endcan

        @can('view candidates')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('candidates.index') }}">
            <i class="material-symbols-rounded opacity-10">person_add</i>
            <span class="nav-link-text mx-2">{{ __('app.candidates') }}</span>
          </a>
        </li>
        @endcan

        @can('view semesters')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('semesters.index') }}">
            <i class="material-symbols-rounded opacity-10">event_list</i>
            <span class="nav-link-text mx-2">{{ __('app.semesters') }}</span>
          </a>
        </li>
        @endcan

        @can('view academic level')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('academic-levels.index') }}">
            <i class="material-symbols-rounded opacity-10">local_library</i>
            <span class="nav-link-text mx-2">{{ __('app.academic levels') }}</span>
          </a>
        </li>
        @endcan

        @can('view classes')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('classes.index') }}">
            <i class="material-symbols-rounded opacity-10">book</i>
            <span class="nav-link-text mx-2">{{ __('app.classes') }}</span>
          </a>
        </li>
        @endcan

        @can('view courses')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('courses.index') }}">
            <i class="material-symbols-rounded opacity-10">book_ribbon</i>
            <span class="nav-link-text mx-2">{{ __('app.courses') }}</span>
          </a>
        </li>
        @endcan

        @can('view partners')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('partners.index') }}">
            <i class="material-symbols-rounded opacity-10">handshake</i>
            <span class="nav-link-text mx-2">{{ __('app.partners') }}</span>
          </a>
        </li>
        @endcan

        @can('view positions')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('positions.index') }}">
            <i class="material-symbols-rounded opacity-10">work</i>
            <span class="nav-link-text mx-2">{{ __('app.positions') }}</span>
          </a>
        </li>
        @endcan

        @canany(['view testimonials'])
        <li class="nav-item mb-1">
          <a class="nav-link text-dark d-flex align-items-center menu" data-bs-toggle="collapse" href="#testimonialsMenu" role="button" aria-expanded="false" aria-controls="testimonialsMenu">
            <i class="material-symbols-rounded opacity-10">rate_review</i>
            <span class="nav-link-text mx-2">{{ __('app.testimonials') }}</span>
          </a>
          <div class="collapse" id="testimonialsMenu">
            <ul class="nav flex-column ms-3">
              <li class="nav-item mb-1">
                <a class="nav-link text-dark" href="{{ route('testimonials.admins') }}">
                  <i class="material-symbols-rounded opacity-10">admin_panel_settings</i>
                  <span class="nav-link-text mx-2">{{ __('app.admins') }}</span>
                </a>
              </li>
              <li class="nav-item mb-1">
                <a class="nav-link text-dark" href="{{ route('testimonials.students') }}">
                  <i class="material-symbols-rounded opacity-10">groups</i>
                  <span class="nav-link-text mx-2">{{ __('app.students') }}</span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        @endcanany

        @canany(['view students', 'view teachers', 'view admins', 'view roles'])
        <li class="nav-item mb-1">
          <a class="nav-link text-dark d-flex align-items-center menu" data-bs-toggle="collapse" href="#userMenu" role="button" aria-expanded="false" aria-controls="userMenu">
            <i class="material-symbols-rounded opacity-10">groups</i>
            <span class="nav-link-text mx-2">{{ __('app.users') }}</span>
          </a>
          <div class="collapse" id="userMenu">
            <ul class="nav flex-column ms-3">
              @can('view students')
              <li class="nav-item mb-1">
                <a class="nav-link text-dark" href="{{ route('students.index') }}">
                  <i class="material-symbols-rounded opacity-10">Groups</i>
                  <span class="nav-link-text mx-2">{{ __('app.students') }}</span>
                </a>
              </li>
              @endcan

              @can('view teachers')
              <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('teachers.index') }}">
                  <i class="material-symbols-rounded opacity-10">co_present</i>
                  <span class="nav-link-text mx-2">{{ __('app.teachers') }}</span>
                </a>
              </li>
              @endcan

              @can('view admins')
              <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('admins.index') }}">
                  <i class="material-symbols-rounded opacity-10">admin_panel_settings</i>
                  <span class="nav-link-text mx-2">{{ __('app.admins') }}</span>
                </a>
              </li>
              @endcan

              @can('view roles')
              <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('roles.index') }}">
                  <i class="material-symbols-rounded opacity-10">assignment_ind</i>
                  <span class="nav-link-text mx-2">{{ __('app.roles') }}</span>
                </a>
              </li>
              @endcan
            </ul>
          </div>
        </li>
        @endcanany
        
        @can('view settings')
        <li class="nav-item mb-1">
          <a class="nav-link text-dark" href="{{ route('settings.index') }}">
            <i class="material-symbols-rounded opacity-10">tune</i>
            <span class="nav-link-text mx-2">{{ __('app.settings') }}</span>
          </a>
        </li>
        @endcan

      </ul>
    </div>
    <div class="sidenav-footer position-absolute w-100 bottom-0 mb-3">
      <div class="mx-3">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button onclick="event.preventDefault(); this.closest('form').submit();" href="route('logout')" class="btn bg-gradient-dark w-100" type="button">{{ __('app.logout') }}</button>
        </form>
      </div>
    </div>
  </aside>