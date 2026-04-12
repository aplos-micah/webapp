<script>
(function () {
    function initProductLookup(widget) {
        const input      = widget.querySelector('.product-lookup__input');
        const hiddenId   = widget.querySelector('.product-lookup__value');
        const nameInput  = document.getElementById(widget.dataset.nameTarget);
        const priceInput = document.getElementById(widget.dataset.priceTarget);
        const results    = widget.querySelector('.product-lookup__results');

        const initialName = widget.dataset.initialName;
        if (initialName) {
            input.value = initialName;
        }

        let debounceTimer = null;
        let activeIndex   = -1;

        function getOptions() {
            return [...results.querySelectorAll('.product-lookup__option')];
        }

        function setActive(index) {
            getOptions().forEach((el, i) => {
                el.classList.toggle('is-active', i === index);
            });
            activeIndex = index;
        }

        function selectOption(item) {
            hiddenId.value = item.id;
            input.value    = item.product_name;
            if (nameInput)  nameInput.value  = item.product_name;
            if (priceInput && item.list_price !== null && priceInput.value === '') {
                priceInput.value = parseFloat(item.list_price).toFixed(2);
                priceInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            results.hidden    = true;
            results.innerHTML = '';
            activeIndex = -1;
        }

        function clearSelection() {
            hiddenId.value = '';
        }

        function renderResults(items) {
            results.innerHTML = '';
            activeIndex = -1;

            if (!items.length) {
                results.innerHTML = '<div class="product-lookup__empty">No products found.</div>';
                results.hidden = false;
                return;
            }

            items.forEach(function (item) {
                const opt = document.createElement('div');
                opt.className = 'product-lookup__option';
                const sku   = item.sku   ? '<span class="product-lookup__sku">' + item.sku + '</span> ' : '';
                const price = item.list_price !== null
                    ? '<span class="product-lookup__price">$' + parseFloat(item.list_price).toLocaleString('en-US', {minimumFractionDigits:2}) + '</span>'
                    : '';
                opt.innerHTML = sku + '<span class="product-lookup__name">' + item.product_name + '</span>' + price;
                opt.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectOption(item);
                });
                results.appendChild(opt);
            });

            results.hidden = false;
        }

        function search(q) {
            if (q.length < 1) {
                results.hidden = true;
                results.innerHTML = '';
                return;
            }
            fetch('/crm/products/search?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(renderResults)
                .catch(function () { results.hidden = true; });
        }

        input.addEventListener('input', function () {
            clearSelection();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                search(input.value.trim());
            }, 200);
        });

        input.addEventListener('keydown', function (e) {
            const opts = getOptions();
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActive(Math.min(activeIndex + 1, opts.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(Math.max(activeIndex - 1, 0));
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && opts[activeIndex]) {
                    opts[activeIndex].dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
                }
            } else if (e.key === 'Escape') {
                results.hidden = true;
                activeIndex = -1;
            }
        });

        input.addEventListener('blur', function () {
            setTimeout(function () { results.hidden = true; }, 150);
        });

        input.addEventListener('focus', function () {
            if (input.value.trim() && !hiddenId.value) {
                search(input.value.trim());
            }
        });
    }

    document.querySelectorAll('.product-lookup').forEach(initProductLookup);

    // -------------------------------------------------------------------------
    // Line item calc: recalculate discount_amount and total_price on change
    // -------------------------------------------------------------------------
    document.querySelectorAll('.line-item-form').forEach(function (form) {
        const unitPriceEl  = form.querySelector('[name="unit_price"]');
        const quantityEl   = form.querySelector('[name="quantity"]');
        const discPctEl    = form.querySelector('[name="discount_percentage"]');
        const discAmtEl    = form.querySelector('[name="discount_amount"]');
        const totalEl      = form.querySelector('[name="total_price"]');

        function recalc() {
            const up  = parseFloat(unitPriceEl?.value)  || 0;
            const qty = parseFloat(quantityEl?.value)   || 1;
            const pct = parseFloat(discPctEl?.value)    || 0;

            let discAmt = pct > 0
                ? Math.round(up * qty * (pct / 100) * 100) / 100
                : parseFloat(discAmtEl?.value || 0);

            if (discAmtEl && pct > 0) {
                discAmtEl.value = discAmt.toFixed(2);
            }

            const total = Math.max(0, Math.round((up * qty - discAmt) * 100) / 100);
            if (totalEl) totalEl.value = total.toFixed(2);
        }

        [unitPriceEl, quantityEl, discPctEl, discAmtEl].forEach(function (el) {
            if (el) el.addEventListener('input', recalc);
        });
    });
})();
</script>
