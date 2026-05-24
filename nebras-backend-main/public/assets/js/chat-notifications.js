(function () {
    'use strict';

    var root = document.getElementById('chat-notifications-root');
    if (!root) return;

    var badge = document.getElementById('chat-notifications-badge');
    var list = document.getElementById('chat-notifications-list');
    var empty = document.getElementById('chat-notifications-empty');
    var countUrl = root.dataset.countUrl;
    var listUrl = root.dataset.listUrl;
    var markReadUrl = root.dataset.markReadUrl;
    var csrf = root.dataset.csrf;

    var failureStreak = 0;
    var pollMs = 60000;
    var timer = null;

    function renderCount(n) {
        if (n > 0) {
            badge.textContent = n > 99 ? '99+' : String(n);
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    function refreshCount() {
        fetch(countUrl + '?p=' + Date.now(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (json) {
                renderCount((json && json.data && json.data.count) || 0);
                if (failureStreak > 0) {
                    failureStreak = 0;
                    if (timer) {
                        clearInterval(timer);
                        timer = setInterval(refreshCount, pollMs);
                    }
                }
            })
            .catch(function () {
                failureStreak++;
                if (failureStreak >= 3 && timer) {
                    clearInterval(timer);
                    timer = setInterval(refreshCount, 5 * 60000);
                }
            });
    }

    function renderItem(item) {
        var time = item.created_at ? new Date(item.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
        var threadLabel = item.thread_type === 'group' ? 'Group' : 'Direct';

        var li = document.createElement('li');
        var a = document.createElement('a');
        a.href = 'javascript:;';
        a.className = 'dropdown-item py-2 px-3 border-bottom';
        a.style.whiteSpace = 'normal';
        a.style.wordBreak = 'break-word';
        a.style.lineHeight = '1.3';
        a.dataset.courseId = String(item.course_id);
        a.dataset.threadType = item.thread_type;
        if (item.thread_partner_id != null) a.dataset.partnerId = String(item.thread_partner_id);

        var topRow = document.createElement('div');
        topRow.className = 'd-flex justify-content-between gap-2';
        var senderEl = document.createElement('strong');
        senderEl.className = 'text-dark small text-truncate';
        senderEl.style.maxWidth = '70%';
        senderEl.textContent = item.sender_name || 'Unknown';
        var timeEl = document.createElement('span');
        timeEl.className = 'text-secondary small flex-shrink-0';
        timeEl.textContent = time;
        topRow.appendChild(senderEl);
        topRow.appendChild(timeEl);

        var subRow = document.createElement('div');
        subRow.className = 'text-secondary small';
        subRow.textContent = (item.course_name || '') + ' · ' + threadLabel;

        var bodyRow = document.createElement('div');
        bodyRow.className = 'small';
        bodyRow.style.display = '-webkit-box';
        bodyRow.style.webkitLineClamp = '2';
        bodyRow.style.webkitBoxOrient = 'vertical';
        bodyRow.style.overflow = 'hidden';
        bodyRow.textContent = item.body_preview || '';

        a.appendChild(topRow);
        a.appendChild(subRow);
        a.appendChild(bodyRow);
        li.appendChild(a);
        return li;
    }

    function loadRecent() {
        fetch(listUrl + '?p=' + Date.now(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                var items = (json && json.data) || [];
                var children = Array.prototype.slice.call(list.children);
                children.forEach(function (child, idx) {
                    if (idx === 0) return; // header li
                    if (child === empty) return;
                    list.removeChild(child);
                });
                if (items.length === 0) {
                    empty.classList.remove('d-none');
                } else {
                    empty.classList.add('d-none');
                    items.forEach(function (item) {
                        var li = renderItem(item);
                        li.firstChild.addEventListener('click', function (ev) {
                            ev.preventDefault();
                            markReadAndNavigate(item);
                        });
                        list.appendChild(li);
                    });
                }
            })
            .catch(function () { /* swallow */ });
    }

    function markReadAndNavigate(item) {
        var formData = new FormData();
        formData.append('_token', csrf);
        formData.append('course_id', String(item.course_id));
        formData.append('thread_type', item.thread_type);
        if (item.thread_type === 'direct' && item.thread_partner_id != null) {
            formData.append('thread_partner_id', String(item.thread_partner_id));
        }
        fetch(markReadUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).finally(function () {
            var url = '/teacher/chat?courseId=' + item.course_id +
                '&threadType=' + item.thread_type +
                (item.thread_partner_id ? '&partnerId=' + item.thread_partner_id : '');
            window.location.href = url;
        });
    }

    document.getElementById('chat-notifications-toggle').addEventListener('click', function () {
        loadRecent();
    });

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') refreshCount();
    });

    refreshCount();
    timer = setInterval(refreshCount, pollMs);
})();
