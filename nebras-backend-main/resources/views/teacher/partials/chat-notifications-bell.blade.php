{{-- chat notifications bell — included from sidebar/topbar --}}
<div class="dropdown me-3" id="chat-notifications-root" data-csrf="{{ csrf_token() }}"
     data-count-url="{{ route('teacher.chat.notifications.count') }}"
     data-list-url="{{ route('teacher.chat.notifications.index') }}"
     data-mark-read-url="{{ route('teacher.chat.notifications.mark-read') }}">
    <a href="javascript:;" class="position-relative d-inline-flex align-items-center text-dark"
       id="chat-notifications-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="material-symbols-rounded">chat</i>
        <span id="chat-notifications-badge"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end p-0 chat-notifications-menu"
        style="min-width: 320px; max-width: 360px; max-height: 420px; overflow-y: auto; z-index: 10000;"
        id="chat-notifications-list">
        <li class="px-3 py-2 fw-bold border-bottom bg-white"
            style="position: sticky; top: 0; z-index: 2;">
            {{ __('app.messages') }}
        </li>
        <li class="px-3 py-3 text-center text-secondary small" id="chat-notifications-empty">
            {{ __('app.no_new_messages') }}
        </li>
    </ul>
</div>
