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
    var sidebar = document.getElementById('side-nav');
    var overlay = document.getElementById('nav-overlay');

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

// ── Collapsible module nav groups ─────────────────────────────────────────────

(function () {
    var modules = Array.prototype.slice.call(
        document.querySelectorAll('.side-nav__module')
    );
    if (!modules.length) return;

    var STORAGE_KEY = 'nav-open-modules';

    function moduleLabel(mod) {
        var span = mod.querySelector('.side-nav__module-toggle span');
        return span ? span.textContent.trim() : '';
    }

    function openModule(mod) {
        mod.classList.add('is-open');
        var btn = mod.querySelector('.side-nav__module-toggle');
        if (btn) btn.setAttribute('aria-expanded', 'true');
    }

    function closeModule(mod) {
        mod.classList.remove('is-open');
        var btn = mod.querySelector('.side-nav__module-toggle');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }

    function loadOpenSet() {
        try { return JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]'); } catch (e) { return []; }
    }

    function saveOpenSet() {
        var open = modules
            .filter(function (m) { return m.classList.contains('is-open'); })
            .map(moduleLabel);
        try { sessionStorage.setItem(STORAGE_KEY, JSON.stringify(open)); } catch (e) {}
    }

    // Priority: always open module with active link; restore others from sessionStorage;
    // if nothing stored and no active module, open first module
    var openSet  = loadOpenSet();
    var hasActive = false;

    modules.forEach(function (mod) {
        var label = moduleLabel(mod);
        if (mod.querySelector('.side-nav__link.is-active')) {
            openModule(mod);
            hasActive = true;
        } else if (openSet.indexOf(label) !== -1) {
            openModule(mod);
        }
    });

    if (!hasActive && openSet.length === 0) {
        openModule(modules[0]);
        saveOpenSet();
    }

    // Toggle on click — each module independent, save full open set
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.side-nav__module-toggle');
        if (!btn) return;
        var mod = btn.closest('.side-nav__module');
        if (!mod) return;
        if (mod.classList.contains('is-open')) {
            closeModule(mod);
        } else {
            openModule(mod);
        }
        saveOpenSet();
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

// ── Proportion bar — set segment widths from data-pct attributes ──────────────

(function () {
    document.querySelectorAll('.proportion-bar__segment[data-pct]').forEach(function (el) {
        el.style.width = el.dataset.pct + '%';
    });
}());

