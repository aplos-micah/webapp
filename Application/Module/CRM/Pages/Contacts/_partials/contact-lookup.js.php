<script>
(function () {
    function initLookup(widget) {
        const input   = widget.querySelector('.contact-lookup__input');
        const hidden  = widget.querySelector('.contact-lookup__value');
        const results = widget.querySelector('.contact-lookup__results');

        const initialName = widget.dataset.initialName;
        if (initialName) {
            input.value = initialName;
        }

        let debounceTimer = null;
        let activeIndex   = -1;

        function getOptions() {
            return [...results.querySelectorAll('.contact-lookup__option')];
        }

        function setActive(index) {
            getOptions().forEach((el, i) => {
                el.classList.toggle('is-active', i === index);
            });
            activeIndex = index;
        }

        function selectOption(id, name) {
            hidden.value = id;
            input.value  = name;
            results.hidden = true;
            results.innerHTML = '';
            activeIndex = -1;
        }

        function clearSelection() {
            hidden.value = '';
        }

        function renderResults(items) {
            results.innerHTML = '';
            activeIndex = -1;

            if (!items.length) {
                results.innerHTML = '<div class="contact-lookup__empty">No contacts found.</div>';
                results.hidden = false;
                return;
            }

            items.forEach(function (item) {
                const opt = document.createElement('div');
                opt.className = 'contact-lookup__option';
                opt.textContent = item.name;
                opt.dataset.id   = item.id;
                opt.dataset.name = item.name;
                opt.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectOption(item.id, item.name);
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
            fetch('/crm/contacts/search?q=' + encodeURIComponent(q))
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
                    const opt = opts[activeIndex];
                    selectOption(opt.dataset.id, opt.dataset.name);
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
            if (input.value.trim() && !hidden.value) {
                search(input.value.trim());
            }
        });
    }

    document.querySelectorAll('.contact-lookup').forEach(initLookup);
})();
</script>
