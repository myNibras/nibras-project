@extends('layouts.teacher')
@section('title'){{ __('app.chat') }} - {{ __('app.teachers') }}@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-dark border-radius-lg text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('app.chat') }}</h6>
                </div>
                <div class="card-body">
                    @if($courses->isEmpty())
                        <div class="text-center py-5">
                            <i class="material-symbols-rounded text-secondary" style="font-size: 64px;">chat</i>
                            <p class="mt-3 mb-0 text-secondary">{{ __('app.chat') }}</p>
                            <p class="text-sm text-secondary">{{ __('app.no results found') }}</p>
                            <p class="text-sm text-muted">{{ __('app.no courses to chat') }}</p>
                        </div>
                    @else
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.select course') }}</label>
                                <select id="chatCourseSelect" class="form-select">
                                    <option value="">{{ __('app.select') }}...</option>
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}">
                                            {{ $c->getLocalizationTitle() }} - {{ $c->classRoom?->getLocalizationName() ?? '—' }} - {{ $c->getLocalizationSchedule() ?? '—' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4" id="chatStudentWrap">
                                <label class="form-label">{{ __('app.select student') }}</label>
                                <select id="chatStudentSelect" class="form-select">
                                    <option value="group">{{ __('app.course chat all students') }}</option>
                                </select>
                            </div>
                        </div>
                        <div id="chatContainer" class="d-none mt-4">
                            <div class="border rounded-3 p-4 bg-light" style="min-height: 400px; max-height: 500px; display: flex; flex-direction: column;">
                                <div id="chatMessages" class="flex-grow-1 overflow-auto mb-3" style="max-height: 350px;"></div>
                                @if(session('impersonate.admin_id'))
                                <div class="alert alert-info mb-0 py-2 small">
                                    {{ __('app.view only mode no changes allowed') }}
                                </div>
                                @else
                                <div id="replyPreview" class="d-none alert alert-secondary py-2 px-3 mb-2">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <strong class="small d-block">{{ __('app.reply') }}</strong>
                                            <span id="replyPreviewText" class="small text-muted"></span>
                                        </div>
                                        <button type="button" id="replyClearBtn" class="btn btn-sm btn-link text-decoration-none p-0">{{ __('app.cancel') }}</button>
                                    </div>
                                </div>
                                <div id="mentionControls" class="mb-2 d-flex gap-2">
                                    <select id="mentionStudentSelect" class="form-select form-select-sm" style="max-width: 260px;">
                                        <option value="">{{ __('app.select student') }}...</option>
                                    </select>
                                    <button type="button" id="mentionAddBtn" class="btn btn-sm btn-outline-secondary">&#64; Add</button>
                                </div>
                                <div id="mentionChips" class="d-flex flex-wrap gap-1 mb-2 d-none"></div>
                                <div class="d-flex gap-2">
                                    <input type="text" id="chatInput" class="form-control" placeholder="{{ __('app.write your message') }}..." maxlength="4000">
                                    <button type="button" id="chatSendBtn" class="btn btn-primary text-nowrap flex-shrink-0">{{ __('app.send') }}</button>
                                </div>
                                @endif
                            </div>
                            <p class="text-muted small mt-2" id="chatHint">{{ __('app.chat with enrolled students') }}</p>
                            <span id="chatHintChannel" class="d-none" data-text="{{ __('app.chat with enrolled students') }}"></span>
                            <span id="chatHintDirect" class="d-none" data-text="{{ __('app.private chat with student') }}"></span>
                        </div>
                        <div id="chatPlaceholder" class="text-center py-5 text-muted">
                            <i class="material-symbols-rounded" style="font-size: 48px;">forum</i>
                            <p class="mt-2 mb-0">{{ __('app.select a course to view chat') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$courses->isEmpty())
<script>
(function() {
    const courseSelect = document.getElementById('chatCourseSelect');
    const studentSelect = document.getElementById('chatStudentSelect');
    const chatHint = document.getElementById('chatHint');
    const container = document.getElementById('chatContainer');
    const placeholder = document.getElementById('chatPlaceholder');
    const messagesEl = document.getElementById('chatMessages');
    const input = document.getElementById('chatInput');
    const sendBtn = document.getElementById('chatSendBtn');
    const replyPreview = document.getElementById('replyPreview');
    const replyPreviewText = document.getElementById('replyPreviewText');
    const replyClearBtn = document.getElementById('replyClearBtn');
    const mentionControls = document.getElementById('mentionControls');
    const mentionStudentSelect = document.getElementById('mentionStudentSelect');
    const mentionAddBtn = document.getElementById('mentionAddBtn');
    const mentionChips = document.getElementById('mentionChips');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let pollInterval = null;
    let replyToMessageId = null;
    let channelParticipants = [];
    let selectedMentionIds = [];

    function formatTime(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function setReplyTarget(msg) {
        replyToMessageId = msg?.id || null;
        if (!replyPreview || !replyPreviewText) return;
        if (!replyToMessageId) {
            replyPreview.classList.add('d-none');
            replyPreviewText.textContent = '';
            return;
        }
        replyPreview.classList.remove('d-none');
        const body = (msg?.body || '').trim();
        const shortBody = body.length > 120 ? body.slice(0, 120) + '...' : body;
        replyPreviewText.textContent = `${msg?.sender_name || ''}: ${shortBody}`;
    }

    function renderMessages(messages) {
        messagesEl.innerHTML = (messages || []).map(m => {
            const isTeacher = m.sender_type === 'teacher';
            const align = isTeacher ? 'end' : 'start';
            const bg = isTeacher ? 'bg-primary text-white' : 'bg-white border';
            const replyBlock = m.reply_to
                ? `<div class="rounded border px-2 py-1 mb-2 ${isTeacher ? 'bg-primary-subtle text-dark border-light' : 'bg-light text-dark'}">
                        <div class="small fw-semibold">${escapeHtml(m.reply_to.sender_name || '')}</div>
                        <div class="small">${linkifyBody(m.reply_to.body || '')}</div>
                   </div>`
                : '';
            const mentionBlock = !isDirect() && Array.isArray(m.mentioned_students) && m.mentioned_students.length
                ? `<div class="mb-2 d-flex gap-1 flex-wrap">
                    ${m.mentioned_students.map(x => `<span class="badge text-bg-light">&#64;${escapeHtml(x.name || '')}</span>`).join('')}
                   </div>`
                : '';
            const likesCount = Number(m.likes_count || 0);
            return `
                <div class="d-flex justify-content-${align} mb-2">
                    <div class="max-w-75 rounded-3 p-3 ${bg}" style="max-width: 75%;">
                        <div class="d-flex align-items-center gap-2 mb-1 ${isTeacher ? 'justify-content-end' : ''}">
                            <strong class="small">${escapeHtml(m.sender_name)}</strong>
                            <span class="small opacity-75">${formatTime(m.created_at)}</span>
                        </div>
                        ${replyBlock}
                        ${mentionBlock}
                        <p class="mb-0" style="white-space: pre-wrap; word-break: break-word;">${linkifyBody(m.body)}</p>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-link btn-sm text-decoration-none px-0 pt-1 ${isTeacher ? 'text-white' : 'text-primary'}" data-reply-id="${m.id}">
                                Reply
                            </button>
                            ${!isDirect() ? `<span class="small ${isTeacher ? 'text-white-50' : 'text-muted'}">❤️ ${likesCount}</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        messagesEl.querySelectorAll('[data-reply-id]').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = Number(this.getAttribute('data-reply-id'));
                const target = (messages || []).find(x => x.id === id);
                setReplyTarget(target || null);
                if (input) input.focus();
            });
        });
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function linkifyBody(text) {
        const escaped = escapeHtml(text || '');
        return escaped.replace(/\b(https?:\/\/[^\s<>"']+|www\.[^\s<>"']+)/gi, function (match) {
            let trailing = '';
            while (match.length && /[.,;:!?)\]>]/.test(match.slice(-1))) {
                trailing = match.slice(-1) + trailing;
                match = match.slice(0, -1);
            }
            const href = /^https?:\/\//i.test(match) ? match : 'https://' + match;
            return '<a href="' + href + '" target="_blank" rel="noopener noreferrer" class="text-decoration-underline">' + match + '</a>' + trailing;
        });
    }

    function isDirect() {
        return !!studentSelect.value && studentSelect.value !== 'group';
    }
    function getStudentId() {
        return isDirect() ? studentSelect.value : null;
    }

    function renderMentionChips() {
        if (!mentionChips) return;
        mentionChips.innerHTML = selectedMentionIds.map(id => {
            const p = channelParticipants.find(x => Number(x.id) === Number(id));
            const name = escapeHtml(p?.name || String(id));
            return `<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" data-mention-remove="${id}">&#64;${name} ×</button>`;
        }).join('');
        mentionChips.classList.toggle('d-none', selectedMentionIds.length === 0);
        mentionChips.querySelectorAll('[data-mention-remove]').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = Number(this.getAttribute('data-mention-remove'));
                selectedMentionIds = selectedMentionIds.filter(x => Number(x) !== id);
                renderMentionChips();
            });
        });
    }

    function loadMessages() {
        const courseId = courseSelect.value;
        if (!courseId) return;
        const studentId = isDirect() ? getStudentId() : null;
        if (isDirect() && !studentId) return;
        const url = studentId
            ? `/teacher/courses/${courseId}/chat/direct/${studentId}/messages`
            : `/teacher/courses/${courseId}/chat/messages`;
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            if (data.status && data.data) renderMessages(data.data);
        })
        .catch(err => console.error(err));
    }

    function sendMessage() {
        if (!input || !sendBtn) return;
        const courseId = courseSelect.value;
        const body = input.value.trim();
        if (!courseId || !body) return;
        const studentId = isDirect() ? getStudentId() : null;
        if (isDirect() && !studentId) return;
        sendBtn.disabled = true;
        const url = studentId
            ? `/teacher/courses/${courseId}/chat/direct/${studentId}/messages`
            : `/teacher/courses/${courseId}/chat/messages`;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                body,
                reply_to_message_id: replyToMessageId || null,
                mentioned_student_ids: !isDirect() ? selectedMentionIds : []
            })
        })
        .then(r => r.json())
        .then(data => {
            input.value = '';
            if (data.status) {
                setReplyTarget(null);
                selectedMentionIds = [];
                renderMentionChips();
                loadMessages();
            }
        })
        .catch(err => console.error(err))
        .finally(() => { sendBtn.disabled = false; });
    }

    function loadEnrolledStudents(courseId) {
        studentSelect.innerHTML = '<option value="group">{{ __('app.course chat all students') }}</option>';
        studentSelect.value = 'group';
        if (mentionStudentSelect) mentionStudentSelect.innerHTML = '<option value="">{{ __('app.select student') }}...</option>';
        if (!courseId) return Promise.resolve();
        return fetch(`/teacher/courses/${courseId}/chat/students`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            if (data.status && data.data) {
                channelParticipants = data.data || [];
                data.data.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name || s.email;
                    studentSelect.appendChild(opt);
                    if (mentionStudentSelect) {
                        const mentionOpt = document.createElement('option');
                        mentionOpt.value = s.id;
                        mentionOpt.textContent = s.name || s.email;
                        mentionStudentSelect.appendChild(mentionOpt);
                    }
                });
            }
        })
        .catch(err => console.error(err));
    }

    function markChatThreadRead() {
        const courseId = courseSelect.value;
        if (!courseId) return;
        const direct = isDirect();
        const studentId = direct ? getStudentId() : null;
        if (direct && !studentId) return;
        const fd = new FormData();
        fd.append('_token', csrfToken || '');
        fd.append('course_id', String(courseId));
        fd.append('thread_type', direct ? 'direct' : 'group');
        if (direct && studentId) fd.append('thread_partner_id', String(studentId));
        fetch('{{ route('teacher.chat.notifications.mark-read') }}', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).catch(function () { /* swallow */ });
    }

    function updateUI() {
        setReplyTarget(null);
        selectedMentionIds = [];
        renderMentionChips();
        const direct = isDirect();
        if (mentionControls) mentionControls.classList.toggle('d-none', direct);
        if (mentionChips) mentionChips.classList.toggle('d-none', direct || selectedMentionIds.length === 0);
        chatHint.textContent = direct
            ? (document.getElementById('chatHintDirect')?.getAttribute('data-text') || 'Private chat with selected student.')
            : (document.getElementById('chatHintChannel')?.getAttribute('data-text') || 'Chat with all enrolled students.');
        loadMessages();
        markChatThreadRead();
    }

    var studentsLoadPromise = Promise.resolve();

    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        if (pollInterval) clearInterval(pollInterval);
        if (!courseId) {
            container.classList.add('d-none');
            placeholder.classList.remove('d-none');
            studentsLoadPromise = Promise.resolve();
            return;
        }
        container.classList.remove('d-none');
        placeholder.classList.add('d-none');
        studentsLoadPromise = loadEnrolledStudents(courseId) || Promise.resolve();
        updateUI();
        pollInterval = setInterval(loadMessages, 5000);
    });

    // Auto-select course / thread from URL query (set by the chat-notifications dropdown).
    // Event-driven: wait for the enrolled-students fetch to resolve before selecting partner.
    setTimeout(function autoSelectFromQuery() {
        try {
            var params = new URLSearchParams(window.location.search);
            var qCourseId = params.get('courseId');
            var qThread = params.get('threadType');
            var qPartner = params.get('partnerId');
            if (!qCourseId) return;
            var matchOption = Array.prototype.find.call(courseSelect.options, function (o) {
                return o.value === qCourseId;
            });
            if (!matchOption) return;
            courseSelect.value = qCourseId;
            courseSelect.dispatchEvent(new Event('change'));
            if (qThread === 'direct' && qPartner) {
                studentsLoadPromise.then(function () {
                    var hasPartner = Array.prototype.find.call(studentSelect.options, function (o) {
                        return o.value === qPartner;
                    });
                    if (hasPartner) {
                        studentSelect.value = qPartner;
                        studentSelect.dispatchEvent(new Event('change'));
                    }
                });
            }
        } catch (e) { /* ignore */ }
    }, 0);

    studentSelect.addEventListener('change', function() {
        updateUI();
    });

    if (sendBtn) sendBtn.addEventListener('click', sendMessage);
    if (replyClearBtn) replyClearBtn.addEventListener('click', function() { setReplyTarget(null); });
    if (mentionAddBtn && mentionStudentSelect) mentionAddBtn.addEventListener('click', function() {
        const id = Number(mentionStudentSelect.value);
        if (!id || selectedMentionIds.includes(id)) return;
        selectedMentionIds.push(id);
        renderMentionChips();
        mentionStudentSelect.value = '';
        const target = channelParticipants.find(x => Number(x.id) === id);
        if (input && target && !input.value.includes(`@${target.name}`)) {
            input.value = `${input.value} @${target.name}`.trim();
        }
    });
    if (input) input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
})();
</script>
@endif
@endsection
