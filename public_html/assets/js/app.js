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

// ── Sidebar navigation search ─────────────────────────────────────────────────

(function () {
    var input = document.getElementById('nav-search');
    if (!input) return;

    var navIndex = [];

    // Index module links (with parent label for context)
    document.querySelectorAll('.side-nav__module').forEach(function (mod) {
        var parentSpan = mod.querySelector('.side-nav__module-toggle span');
        var parentLabel = parentSpan ? parentSpan.textContent.trim() : '';
        mod.querySelectorAll('.side-nav__module-links li').forEach(function (li) {
            var a = li.querySelector('.side-nav__link');
            if (!a) return;
            var span = a.querySelector('span');
            navIndex.push({
                li: li, mod: mod,
                text: (parentLabel + ' ' + (span ? span.textContent.trim() : '')).toLowerCase(),
            });
        });
    });

    // Index standalone links (Dashboard, My Company, My Profile)
    document.querySelectorAll('.side-nav__list > li > .side-nav__link').forEach(function (a) {
        var span = a.querySelector('span');
        navIndex.push({
            li: a.parentElement, mod: null,
            text: (span ? span.textContent.trim() : '').toLowerCase(),
        });
    });

    function applySearch(q) {
        var matchedMods = [];
        navIndex.forEach(function (item) {
            var matches = item.text.indexOf(q) !== -1;
            item.li.style.display = matches ? '' : 'none';
            if (matches && item.mod && matchedMods.indexOf(item.mod) === -1) {
                matchedMods.push(item.mod);
            }
        });
        document.querySelectorAll('.side-nav__module').forEach(function (mod) {
            var hasMatch = matchedMods.indexOf(mod) !== -1;
            mod.style.display = hasMatch ? '' : 'none';
            var links = mod.querySelector('.side-nav__module-links');
            if (links) links.style.display = hasMatch ? 'block' : '';
        });
    }

    function clearSearch() {
        navIndex.forEach(function (item) { item.li.style.display = ''; });
        document.querySelectorAll('.side-nav__module').forEach(function (mod) {
            mod.style.display = '';
            var links = mod.querySelector('.side-nav__module-links');
            if (links) links.style.display = '';
        });
    }

    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        q ? applySearch(q) : clearSearch();
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { input.value = ''; clearSearch(); input.blur(); }
    });
}());

// ── Tab switching (generic) ───────────────────────────────────────────────────

(function () {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-tab-target]');
        if (!btn) return;
        var bar = btn.closest('.tab-bar');
        if (!bar) return;
        var target    = btn.dataset.tabTarget;
        var container = bar.parentElement;

        bar.querySelectorAll('[data-tab-target]').forEach(function (b) {
            b.classList.remove('profile-tab--active');
        });
        btn.classList.add('profile-tab--active');

        var panelIds = Array.from(bar.querySelectorAll('[data-tab-target]'))
            .map(function (b) { return b.dataset.tabTarget; });
        panelIds.forEach(function (id) {
            var p = container.querySelector('#' + id);
            if (p) p.hidden = (id !== target);
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

