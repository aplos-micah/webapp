# JavaScript Governance

When to write JavaScript, where it lives, and how it must be structured in AplosSuite.

---

## Core Rule: Progressive Enhancement

JavaScript enhances the platform — it never replaces server-rendered behaviour. Every page must be functional without JavaScript before JS is added.

- Forms submit via standard HTML `POST`
- Navigation works via standard anchor links
- Data is rendered server-side in PHP views
- JavaScript adds: live search, sidebar toggle, drag-and-drop, toast dismissal, UI polish

---

## File Structure

Exactly three possible JS files per module/area:

| File | Loaded on | Purpose |
|------|-----------|---------|
| `public_html/assets/js/app.js` | Every control-panel page | Platform shell behaviour: sidebar toggle, toast auto-dismiss, shared utilities |
| `public_html/assets/js/{module}.js` | Pages within `/{module}/*` only | Module-specific behaviour (e.g. drag-and-drop in CRM, chart rendering) |
| `public_html/assets/vendor/htmx.min.js` | Every control-panel page | HTMX — live search and partial page updates |

The template auto-loads `{module}.js` if the file exists for the current module segment. No manual script tags are needed in views.

**Never create:**
- Inline `<script>` blocks in a view or template
- A per-page JS file (no `tickets-list.js`, `asset-details.js`)
- A second vendor JS file without explicit approval
- Any ES module (`import`/`export`) — all files use plain IIFE or function scope

---

## When to Create a Module JS File

Create `public_html/assets/js/{module}.js` only when the module needs behaviour that:
- Cannot be achieved with HTMX alone
- Is specific enough to that module that it would not apply to any other module

Current module JS files:
- `crm.js` — drag-and-drop tile reordering on account/contact detail pages
- `admin.js` — any admin-specific UI behaviour

If the behaviour could apply to any module (e.g. a generic confirmation dialog), put it in `app.js` instead.

---

## HTMX — Live Search Pattern

HTMX is the approved approach for live search on list pages. It performs a partial page swap without a full reload.

### Standard pattern

```html
<input
    type="search"
    name="search"
    class="input list-search__input"
    hx-get="/{module}/{entity}/list"
    hx-trigger="input delay:300ms"
    hx-target="#results-container"
    hx-select="#results-container"
    hx-swap="outerHTML"
    hx-include="closest form"
>
```

| Attribute | Value | Purpose |
|-----------|-------|---------|
| `hx-get` | Same URL as the page | The server renders the full page; HTMX extracts only the target |
| `hx-trigger` | `input delay:300ms` | Debounce — waits 300 ms after the last keypress |
| `hx-target` | `#results-container` | The element to replace |
| `hx-select` | `#results-container` | Extract only this element from the server response |
| `hx-swap` | `outerHTML` | Replace the entire target element including itself |
| `hx-include` | `closest form` | Include all other form fields (filters, sort) in the request |

The results container must have a matching `id`:
```html
<div id="results-container">
    <!-- table or empty state rendered by view.php -->
</div>
```

### Rules

- Always debounce live search with `delay:300ms` — never trigger on every keystroke without a delay
- Always use `hx-select` to extract only the results container — the full page is rendered server-side and HTMX picks the relevant part
- Do not use HTMX for form submissions — use standard HTML `POST` with PRG

---

## Writing JavaScript

### Structure

All JS must be wrapped in an IIFE or guard to avoid polluting the global scope:

```js
// Correct — IIFE
(function () {
    var el = document.getElementById('my-element');
    if (!el) return;
    // …
}());

// Correct — guard at top level when the script is scoped to one file
var el = document.getElementById('my-element');
if (!el) return; // but only valid at top level of a module file
```

### DOM readiness

All JS in `app.js` and module files runs after `defer` — the DOM is ready when the script executes. Do not wrap code in `DOMContentLoaded` unless attaching a listener inside an async callback.

### Event delegation

Attach event listeners to a stable ancestor rather than individual dynamic elements:

```js
// Correct — one listener on a stable parent
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.toast__close');
    if (btn) btn.closest('.toast').remove();
});

// Wrong — attaches to each element, breaks when DOM updates
document.querySelectorAll('.toast__close').forEach(function (btn) {
    btn.addEventListener('click', function () { … });
});
```

### No inline event handlers

Do not use `onclick=`, `onchange=`, or other inline event handler attributes in HTML. Attach all listeners in JS files.

```html
<!-- Wrong -->
<button onclick="doSomething()">Click</button>

<!-- Correct -->
<button id="my-btn">Click</button>
<!-- in app.js or module.js: -->
<!-- document.getElementById('my-btn').addEventListener('click', doSomething); -->
```

Exception: `onchange="this.form.submit()"` on filter `<select>` elements in list pages is acceptable — it is a one-liner that degrades gracefully.

### `var` vs `let`/`const`

Use `var` in `app.js` for maximum compatibility with the existing codebase. New module JS files may use `const`/`let` if the target browser set supports ES6 (all modern browsers do).

---

## What Belongs in app.js vs a Module File

| Behaviour | Where |
|-----------|-------|
| Sidebar open/close toggle | `app.js` |
| Toast auto-dismiss and close button | `app.js` |
| Proportion bar / chart initialisation used on home dashboard | `app.js` |
| Generic confirmation dialog | `app.js` |
| CRM tile drag-and-drop reordering | `crm.js` |
| Module-specific chart or visualisation | `{module}.js` |
| Live search (HTMX) | HTML attributes in the view — no JS file needed |

If you are unsure, put it in `app.js`. A module file is only justified when the code references a DOM element that only exists in that module.

---

## What Not to Do

- Do not write `<script>` blocks inside view files or templates
- Do not use `document.write()`
- Do not use jQuery or any additional JS library without approval
- Do not use ES modules (`import`/`export`) — stick to plain scripts
- Do not use HTMX for form POST submissions — use standard HTML forms with PRG
- Do not trigger HTMX live search on every keystroke — always use `delay:300ms`
- Do not attach event listeners to elements that may not exist — always guard with `if (!el) return`
- Do not create per-page JS files
