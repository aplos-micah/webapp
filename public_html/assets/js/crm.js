// ── CRM: related-tile drag-and-drop reordering ────────────────────────────────
// Requires <div id="related-tiles" data-save-url="/crm/.../savelayout">

(function () {
    var container = document.getElementById('related-tiles');
    if (!container) return;

    var saveUrl  = container.dataset.saveUrl;
    var dragging = null;

    container.addEventListener('dragstart', function (e) {
        dragging = e.target.closest('[data-tile]');
        if (!dragging) return;
        dragging.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
    });

    container.addEventListener('dragend', function () {
        if (dragging) dragging.classList.remove('is-dragging');
        container.querySelectorAll('[data-tile]').forEach(function (el) { el.classList.remove('drag-over'); });
        dragging = null;
        if (!saveUrl) return;
        var order = Array.from(container.querySelectorAll('[data-tile]')).map(function (el) { return el.dataset.tile; });
        fetch(saveUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ order: order }) });
    });

    container.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        var target = e.target.closest('[data-tile]');
        if (!target || target === dragging) return;
        container.querySelectorAll('[data-tile]').forEach(function (el) { el.classList.remove('drag-over'); });
        target.classList.add('drag-over');
        var after = e.clientY > target.getBoundingClientRect().top + target.getBoundingClientRect().height / 2;
        container.insertBefore(dragging, after ? target.nextSibling : target);
    });

    container.addEventListener('dragleave', function (e) {
        var target = e.target.closest('[data-tile]');
        if (target) target.classList.remove('drag-over');
    });
}());

// ── CRM: Opportunities — line item add/cancel, inline editing, mobile tabs ────

(function () {
    var toggle    = document.getElementById('line-item-toggle');
    var wrap      = document.getElementById('line-item-form-wrap');
    var cancelBtn = document.getElementById('line-item-cancel');

    if (toggle && wrap && cancelBtn) {
        toggle.addEventListener('click', function () {
            wrap.hidden = !wrap.hidden;
            if (!wrap.hidden) {
                var first = wrap.querySelector('.entity-lookup__input');
                if (first) first.focus();
            }
        });
        cancelBtn.addEventListener('click', function () { wrap.hidden = true; });
    }

    // Inline line-item edit rows (double-click or edit button)
    function openLI(id) {
        var dispRow = document.querySelector('tr[data-li-id="' + id + '"]');
        var editRow = document.getElementById('li-edit-' + id);
        if (!editRow) return;
        if (dispRow) dispRow.hidden = true;
        editRow.hidden = false;
        var first = editRow.querySelector('.input');
        if (first) first.focus();
    }

    function closeLI(id) {
        var dispRow = document.querySelector('tr[data-li-id="' + id + '"]');
        var editRow = document.getElementById('li-edit-' + id);
        if (!editRow) return;
        editRow.hidden = true;
        if (dispRow) dispRow.hidden = false;
    }

    document.querySelectorAll('.li-edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { openLI(btn.dataset.li); });
    });

    document.querySelectorAll('tr[data-li-id]').forEach(function (row) {
        row.style.cursor = 'pointer';
        row.addEventListener('dblclick', function () { openLI(row.dataset.liId); });
    });

    document.querySelectorAll('.li-edit-cancel').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var editRow = btn.closest('.inline-edit-row');
            if (editRow) closeLI(editRow.id.replace('li-edit-', ''));
        });
    });

    // Mobile tab switching
    var tabBar        = document.getElementById('tab-bar');
    var panelDetail   = document.getElementById('opp-panel-detail');
    var panelLineItems = document.getElementById('opp-panel-line-items');

    if (tabBar && panelDetail && panelLineItems) {
        tabBar.addEventListener('click', function (e) {
            var btn = e.target.closest('.tab-bar__btn');
            if (!btn) return;
            tabBar.querySelectorAll('.tab-bar__btn').forEach(function (b) {
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('is-active');
            btn.setAttribute('aria-selected', 'true');
            var tab = btn.dataset.tab;
            panelDetail.classList.toggle('is-active', tab === 'detail');
            panelLineItems.classList.toggle('is-active', tab === 'line-items');
        });
    }
}());

// ── CRM: Account lookup autocomplete ─────────────────────────────────────────

(function () {
    function initLookup(widget) {
        var input   = widget.querySelector('.entity-lookup__input');
        var hidden  = widget.querySelector('.entity-lookup__value');
        var results = widget.querySelector('.entity-lookup__results');
        if (!input || !hidden || !results) return;

        if (widget.dataset.initialName) input.value = widget.dataset.initialName;

        var debounceTimer = null;
        var activeIndex   = -1;

        function getOptions() { return Array.from(results.querySelectorAll('.entity-lookup__option')); }

        function setActive(i) {
            getOptions().forEach(function (el, j) { el.classList.toggle('is-active', j === i); });
            activeIndex = i;
        }

        function selectOption(id, name) {
            hidden.value = id; input.value = name;
            results.hidden = true; results.innerHTML = ''; activeIndex = -1;
        }

        function renderResults(items) {
            results.innerHTML = ''; activeIndex = -1;
            if (!items.length) {
                results.innerHTML = '<div class="entity-lookup__empty">No accounts found.</div>';
                results.hidden = false; return;
            }
            items.forEach(function (item) {
                var opt = document.createElement('div');
                opt.className = 'entity-lookup__option';
                opt.textContent = item.name;
                opt.addEventListener('mousedown', function (e) { e.preventDefault(); selectOption(item.id, item.name); });
                results.appendChild(opt);
            });
            results.hidden = false;
        }

        function search(q) {
            if (q.length < 1) { results.hidden = true; results.innerHTML = ''; return; }
            fetch('/crm/accounts/search?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); }).then(renderResults)
                .catch(function () { results.hidden = true; });
        }

        input.addEventListener('input', function () {
            hidden.value = '';
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { search(input.value.trim()); }, 200);
        });

        input.addEventListener('keydown', function (e) {
            var opts = getOptions();
            if (e.key === 'ArrowDown')  { e.preventDefault(); setActive(Math.min(activeIndex + 1, opts.length - 1)); }
            else if (e.key === 'ArrowUp')  { e.preventDefault(); setActive(Math.max(activeIndex - 1, 0)); }
            else if (e.key === 'Enter') { e.preventDefault(); if (activeIndex >= 0 && opts[activeIndex]) { selectOption(opts[activeIndex].dataset.id, opts[activeIndex].dataset.name); } }
            else if (e.key === 'Escape') { results.hidden = true; activeIndex = -1; }
        });

        input.addEventListener('blur',  function () { setTimeout(function () { results.hidden = true; }, 150); });
        input.addEventListener('focus', function () { if (input.value.trim() && !hidden.value) search(input.value.trim()); });
    }

    document.querySelectorAll('.entity-lookup').forEach(initLookup);
}());

// ── CRM: Contact lookup autocomplete ─────────────────────────────────────────

(function () {
    function initLookup(widget) {
        var input   = widget.querySelector('.entity-lookup__input');
        var hidden  = widget.querySelector('.entity-lookup__value');
        var results = widget.querySelector('.entity-lookup__results');
        if (!input || !hidden || !results) return;

        if (widget.dataset.initialName) input.value = widget.dataset.initialName;

        var debounceTimer = null;
        var activeIndex   = -1;

        function getOptions() { return Array.from(results.querySelectorAll('.entity-lookup__option')); }

        function setActive(i) {
            getOptions().forEach(function (el, j) { el.classList.toggle('is-active', j === i); });
            activeIndex = i;
        }

        function selectOption(id, name) {
            hidden.value = id; input.value = name;
            results.hidden = true; results.innerHTML = ''; activeIndex = -1;
        }

        function renderResults(items) {
            results.innerHTML = ''; activeIndex = -1;
            if (!items.length) {
                results.innerHTML = '<div class="entity-lookup__empty">No contacts found.</div>';
                results.hidden = false; return;
            }
            items.forEach(function (item) {
                var opt = document.createElement('div');
                opt.className = 'entity-lookup__option';
                opt.textContent = item.name;
                opt.addEventListener('mousedown', function (e) { e.preventDefault(); selectOption(item.id, item.name); });
                results.appendChild(opt);
            });
            results.hidden = false;
        }

        function search(q) {
            if (q.length < 1) { results.hidden = true; results.innerHTML = ''; return; }
            fetch('/crm/contacts/search?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); }).then(renderResults)
                .catch(function () { results.hidden = true; });
        }

        input.addEventListener('input', function () {
            hidden.value = '';
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { search(input.value.trim()); }, 200);
        });

        input.addEventListener('keydown', function (e) {
            var opts = getOptions();
            if (e.key === 'ArrowDown')  { e.preventDefault(); setActive(Math.min(activeIndex + 1, opts.length - 1)); }
            else if (e.key === 'ArrowUp')  { e.preventDefault(); setActive(Math.max(activeIndex - 1, 0)); }
            else if (e.key === 'Enter') { e.preventDefault(); if (activeIndex >= 0 && opts[activeIndex]) { selectOption(opts[activeIndex].dataset.id, opts[activeIndex].dataset.name); } }
            else if (e.key === 'Escape') { results.hidden = true; activeIndex = -1; }
        });

        input.addEventListener('blur',  function () { setTimeout(function () { results.hidden = true; }, 150); });
        input.addEventListener('focus', function () { if (input.value.trim() && !hidden.value) search(input.value.trim()); });
    }

    document.querySelectorAll('.entity-lookup').forEach(initLookup);
}());

// ── CRM: Product lookup autocomplete + line item price calculation ─────────────

(function () {
    function initProductLookup(widget) {
        var input      = widget.querySelector('.entity-lookup__input');
        var hiddenId   = widget.querySelector('.entity-lookup__value');
        var nameInput  = document.getElementById(widget.dataset.nameTarget);
        var priceInput = document.getElementById(widget.dataset.priceTarget);
        var results    = widget.querySelector('.entity-lookup__results');
        if (!input || !hiddenId || !results) return;

        if (widget.dataset.initialName) input.value = widget.dataset.initialName;

        var debounceTimer = null;
        var activeIndex   = -1;

        function getOptions() { return Array.from(results.querySelectorAll('.entity-lookup__option')); }

        function setActive(i) {
            getOptions().forEach(function (el, j) { el.classList.toggle('is-active', j === i); });
            activeIndex = i;
        }

        function selectOption(item) {
            hiddenId.value = item.id;
            input.value    = item.product_name;
            if (nameInput) nameInput.value = item.product_name;
            if (priceInput && item.list_price !== null && priceInput.value === '') {
                priceInput.value = parseFloat(item.list_price).toFixed(2);
                priceInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            results.hidden = true; results.innerHTML = ''; activeIndex = -1;
        }

        function renderResults(items) {
            results.innerHTML = ''; activeIndex = -1;
            if (!items.length) {
                results.innerHTML = '<div class="entity-lookup__empty">No products found.</div>';
                results.hidden = false; return;
            }
            items.forEach(function (item) {
                var opt = document.createElement('div');
                opt.className = 'entity-lookup__option';
                var sku   = item.sku ? '<span class="entity-lookup__meta">' + item.sku + '</span> ' : '';
                var price = item.list_price !== null
                    ? '<span class="entity-lookup__price">$' + parseFloat(item.list_price).toLocaleString('en-US', { minimumFractionDigits: 2 }) + '</span>'
                    : '';
                opt.innerHTML = sku + '<span class="entity-lookup__name">' + item.product_name + '</span>' + price;
                opt.addEventListener('mousedown', function (e) { e.preventDefault(); selectOption(item); });
                results.appendChild(opt);
            });
            results.hidden = false;
        }

        function search(q) {
            if (q.length < 1) { results.hidden = true; results.innerHTML = ''; return; }
            fetch('/crm/products/search?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); }).then(renderResults)
                .catch(function () { results.hidden = true; });
        }

        input.addEventListener('input', function () {
            hiddenId.value = '';
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { search(input.value.trim()); }, 200);
        });

        input.addEventListener('keydown', function (e) {
            var opts = getOptions();
            if (e.key === 'ArrowDown')  { e.preventDefault(); setActive(Math.min(activeIndex + 1, opts.length - 1)); }
            else if (e.key === 'ArrowUp')  { e.preventDefault(); setActive(Math.max(activeIndex - 1, 0)); }
            else if (e.key === 'Enter') { e.preventDefault(); if (activeIndex >= 0 && opts[activeIndex]) { opts[activeIndex].dispatchEvent(new MouseEvent('mousedown', { bubbles: true })); } }
            else if (e.key === 'Escape') { results.hidden = true; activeIndex = -1; }
        });

        input.addEventListener('blur',  function () { setTimeout(function () { results.hidden = true; }, 150); });
        input.addEventListener('focus', function () { if (input.value.trim() && !hiddenId.value) search(input.value.trim()); });
    }

    document.querySelectorAll('.entity-lookup').forEach(initProductLookup);


    // Line item price recalculation
    document.querySelectorAll('.line-item-form').forEach(function (form) {
        var unitPriceEl = form.querySelector('[name="unit_price"]');
        var quantityEl  = form.querySelector('[name="quantity"]');
        var discPctEl   = form.querySelector('[name="discount_percentage"]');
        var discAmtEl   = form.querySelector('[name="discount_amount"]');
        var totalEl     = form.querySelector('[name="total_price"]');

        function recalc() {
            var up      = parseFloat(unitPriceEl && unitPriceEl.value) || 0;
            var qty     = parseFloat(quantityEl  && quantityEl.value)  || 1;
            var pct     = parseFloat(discPctEl   && discPctEl.value)   || 0;
            var discAmt = pct > 0
                ? Math.round(up * qty * (pct / 100) * 100) / 100
                : parseFloat((discAmtEl && discAmtEl.value) || 0);
            if (discAmtEl && pct > 0) discAmtEl.value = discAmt.toFixed(2);
            var total = Math.max(0, Math.round((up * qty - discAmt) * 100) / 100);
            if (totalEl) totalEl.value = total.toFixed(2);
        }

        [unitPriceEl, quantityEl, discPctEl, discAmtEl].forEach(function (el) {
            if (el) el.addEventListener('input', recalc);
        });
    });
}());

// ── CRM: Performance bar — set segment widths from data-pct attributes ─────────

(function () {
    document.querySelectorAll('.proportion-bar__segment[data-pct]').forEach(function (el) {
        el.style.width = el.dataset.pct + '%';
    });
}());
