// ── Inline-edit row helpers ───────────────────────────────────────────────────

function openInlineEdit(attr, prefix) {
    return function (id) {
        var dispRow = document.querySelector('tr[' + attr + '="' + id + '"]');
        var editRow = document.getElementById(prefix + id);
        if (!editRow) return;
        if (dispRow) dispRow.hidden = true;
        editRow.hidden = false;
        var firstInput = editRow.querySelector('.input');
        if (firstInput) firstInput.focus();
    };
}

function closeInlineEdit(attr, prefix) {
    return function (id) {
        var dispRow = document.querySelector('tr[' + attr + '="' + id + '"]');
        var editRow = document.getElementById(prefix + id);
        if (!editRow) return;
        editRow.hidden = true;
        if (dispRow) dispRow.hidden = false;
    };
}

// ── User list ─────────────────────────────────────────────────────────────────

(function () {
    var open  = openInlineEdit('data-user-id', 'user-edit-');
    var close = closeInlineEdit('data-user-id', 'user-edit-');

    document.querySelectorAll('tr[data-user-id]').forEach(function (row) {
        row.addEventListener('dblclick', function () { open(row.dataset.userId); });
    });

    document.querySelectorAll('.user-edit-cancel').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var editRow = btn.closest('.inline-edit-row');
            if (editRow) close(editRow.id.replace('user-edit-', ''));
        });
    });
}());

// ── Company list ──────────────────────────────────────────────────────────────

(function () {
    var open  = openInlineEdit('data-company-id', 'company-edit-');
    var close = closeInlineEdit('data-company-id', 'company-edit-');

    document.querySelectorAll('tr[data-company-id]').forEach(function (row) {
        row.addEventListener('dblclick', function () { open(row.dataset.companyId); });
    });

    document.querySelectorAll('.company-edit-cancel').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var editRow = btn.closest('.inline-edit-row');
            if (editRow) close(editRow.id.replace('company-edit-', ''));
        });
    });
}());

// ── OAuth token list (ManageOAuth page) ───────────────────────────────────────

(function () {
    var open  = openInlineEdit('data-token-id', 'token-edit-');
    var close = closeInlineEdit('data-token-id', 'token-edit-');

    document.querySelectorAll('tr[data-token-id]').forEach(function (row) {
        row.addEventListener('dblclick', function () { open(row.dataset.tokenId); });
    });

    document.querySelectorAll('.token-edit-cancel').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var editRow = btn.closest('.inline-edit-row');
            if (editRow) close(editRow.id.replace('token-edit-', ''));
        });
    });

    // Register client panel toggle
    var registerPanel  = document.getElementById('register-client-panel');
    var registerToggle = document.getElementById('register-client-toggle');
    var registerCancel = document.getElementById('register-client-cancel');

    if (registerToggle && registerPanel) {
        registerToggle.addEventListener('click', function () {
            registerPanel.hidden = !registerPanel.hidden;
            if (!registerPanel.hidden) {
                var firstInput = registerPanel.querySelector('.input');
                if (firstInput) firstInput.focus();
            }
        });
    }

    if (registerCancel && registerPanel) {
        registerCancel.addEventListener('click', function () { registerPanel.hidden = true; });
    }
}());

// ── LogViewer — checkbox select-all and toolbar ───────────────────────────────

(function () {
    function initTable(selectAllId, cbClass, toolbarId, countId, clearBtnId) {
        var selectAll = document.getElementById(selectAllId);
        var toolbar   = document.getElementById(toolbarId);
        var countEl   = document.getElementById(countId);
        var clearBtn  = document.getElementById(clearBtnId);
        if (!selectAll || !toolbar) return;

        function getCbs() { return document.querySelectorAll('.' + cbClass); }

        function update() {
            var cbs     = getCbs();
            var checked = Array.from(cbs).filter(function (c) { return c.checked; });
            var n       = checked.length;
            toolbar.style.display = n > 0 ? 'flex' : 'none';
            if (countEl)   countEl.textContent     = n + ' selected';
            if (selectAll) selectAll.indeterminate = n > 0 && n < cbs.length;
            if (selectAll) selectAll.checked       = n > 0 && n === cbs.length;
        }

        selectAll.addEventListener('change', function () {
            getCbs().forEach(function (cb) { cb.checked = selectAll.checked; });
            update();
        });

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains(cbClass)) update();
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                getCbs().forEach(function (cb) { cb.checked = false; });
                selectAll.checked = false;
                update();
            });
        }
    }

    initTable('entries-select-all',  'entry-cb',   'entries-toolbar',  'entries-count',  'entries-clear-sel');
    initTable('archives-select-all', 'archive-cb', 'archives-toolbar', 'archives-count', 'archives-clear-sel');
}());
