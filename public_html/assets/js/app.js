// ── Toast ─────────────────────────────────────────────────────────────────────

// Close button (replaces inline onclick handler)
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.toast__close');
    if (btn) btn.closest('.toast').remove();
});

// Auto-dismiss after 5 s
(function () {
    var t = document.getElementById('app-toast');
    if (!t) return;
    setTimeout(function () {
        t.classList.add('toast--hiding');
        setTimeout(function () { t.remove(); }, 400);
    }, 5000);
}());

// ── Sidebar toggle (ControlPanel template) ────────────────────────────────────

(function () {
    var toggle  = document.getElementById('sidebar-toggle');
    var sidebar = document.getElementById('app-sidebar');
    var overlay = document.getElementById('sidebar-overlay');

    if (!toggle || !sidebar || !overlay) return;

    function openSidebar() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-visible');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Close navigation menu');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Open navigation menu');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function () {
        if (sidebar.classList.contains('is-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) closeSidebar();
    });
}());

// ── Profile page — tab switching ──────────────────────────────────────────────

(function () {
    document.querySelectorAll('[data-tab]').forEach(function (btn) {
        if (!btn.id || !btn.id.startsWith('tab-btn-')) return;
        btn.addEventListener('click', function () {
            var name = btn.dataset.tab;
            ['company', 'password'].forEach(function (t) {
                var panel = document.getElementById('tab-panel-' + t);
                var tabBtn = document.getElementById('tab-btn-' + t);
                if (panel) panel.hidden = (t !== name);
                if (tabBtn) tabBtn.classList.toggle('profile-tab--active', t === name);
            });
        });
    });
}());

// ── Email verification countdown redirect ─────────────────────────────────────

(function () {
    var card = document.getElementById('verify-redirect');
    if (!card) return;

    var dest = card.dataset.redirect;
    if (!dest) return;

    var el = document.getElementById('countdown');

    setTimeout(function () { window.location.href = dest; }, 3000);

    var remaining = 3;
    var tick = setInterval(function () {
        remaining -= 1;
        if (el) el.textContent = remaining;
        if (remaining <= 0) clearInterval(tick);
    }, 1000);
}());

