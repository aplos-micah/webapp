# Routing Governance

How URLs resolve to pages, how auth gates work, and the conventions controllers must follow.

---

## Auto-Discovery — No Registration Required

Routes are not registered anywhere. The router (`Application/router.php`) resolves every URL by scanning the filesystem. Adding a page folder is all that is needed.

### Resolution rules

| URL pattern | Resolves to |
|-------------|-------------|
| `/home` | `Application/Pages/Home/` |
| `/login` | `Application/Pages/Login/` |
| `/{module}/{entity}/{action}` | `Application/Module/{Module}/Pages/{Entity}/{Action}/` |
| `/api_v2/{module}/{endpoint}` | `Application/Module/{Module}/Api/{Endpoint}/controller.php` |

Folder matching is **case-insensitive** — `/itsm/tickets/list` resolves to `Module/ITSM/Pages/Tickets/List/`.

### Three-file page

Every page folder contains exactly three files:

```
config.php      — template selection and auth overrides
controller.php  — data loading, POST handling, redirects
view.php        — HTML output only, no logic
```

---

## URL Conventions

```
/{module}/{entity}/{action}
```

| Segment | Rule | Examples |
|---------|------|---------|
| `{module}` | Lowercase module name | `itsm`, `crm`, `assets`, `kb`, `admin` |
| `{entity}` | Lowercase plural entity name | `tickets`, `accounts`, `assets`, `articles` |
| `{action}` | Lowercase action name | `list`, `new`, `details` |

### Standard actions

Every primary entity has these three pages:

| Action | URL | Purpose |
|--------|-----|---------|
| `list` | `/{module}/{entity}/list` | Paginated, searchable, filterable table |
| `new` | `/{module}/{entity}/new` | Create form |
| `details` | `/{module}/{entity}/details?id={id}` | View + edit toggle for a single record |

Module-level pages (not entity-specific):

| Action | URL | Purpose |
|--------|-----|---------|
| `dashboard` | `/{module}/dashboard` | Module home with stat cards and summaries |
| `guide` | `/{module}/guide` | User guide and playbooks |

---

## config.php

Every page has a `config.php` that returns an array. The module-level `module.php` is merged first; `config.php` values take precedence.

```php
<?php
return ['template' => 'ControlPanel.php'];
```

Available keys:

| Key | Type | Default | Purpose |
|-----|------|---------|---------|
| `template` | string | `'default.php'` | Template file in `Application/Templates/` |
| `requiresLogin` | bool | inherited from `module.php` | Redirect to `/login` if not authenticated |
| `requiresUserType` | string\|null | inherited from `module.php` | Restrict to a specific user type |

Most module pages only need `['template' => 'ControlPanel.php']` — the module's `module.php` handles auth.

---

## NCND Auth Gate — Neither Confirm Nor Deny

Unauthenticated requests to any protected page are redirected to `/login`. The platform never reveals whether a URL exists. A user without access sees the login page, not a 403 or 404.

This is enforced by the router — controllers do not need to check `isLoggedIn()`.

Module access (e.g. `requiresModuleItsm`) is enforced the same way. A user without the module entitlement is redirected to `/login`, not shown an error.

---

## POST → Redirect → GET (PRG)

All form submissions must follow PRG. A successful POST always ends with a redirect, never a rendered view.

```php
// Correct — POST handler in controller.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $obj->create($data);
    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Record created.'];
        return Response::redirect('/{module}/{entity}/details?id=' . $result['id']);
    }
    $error = $result['error'];
    // Falls through to render the view with the error
}
```

**Rules:**
- Success: redirect to the detail page of the created/updated record
- Failure: set `$error`, fall through to render the view with the form pre-populated
- Never redirect to the list on success from a detail edit — redirect back to the detail page
- After delete, redirect to the list

### Flash messages

Flash messages are stored in `$_SESSION['_flash']` before a redirect and consumed by the template on the next page load.

```php
$_SESSION['_flash'] = ['type' => 'success', 'message' => 'Ticket created successfully.'];
```

Valid `type` values: `success`, `error`, `warning`, `info`.

---

## controller.php Rules

1. **Start with session** — if the controller accesses `$_SESSION`, call `session_start()` at the top:
   ```php
   if (session_status() === PHP_SESSION_NONE) {
       session_start();
   }
   ```
2. **Return a Response** — never call `header()`, `exit`, or `die` directly. Use `Response::redirect()` or `Response::json()` and `return` the result.
3. **No HTML output** — controllers produce data, not markup. All HTML goes in `view.php`.
4. **Guard invalid IDs early** — details and edit pages redirect to the list if the ID is missing or the record is not found:
   ```php
   $id = (int) ($_GET['id'] ?? 0);
   if ($id < 1) return Response::redirect('/{module}/{entity}/list');
   $record = $obj->findById($id);
   if (!$record) {
       $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Record not found.'];
       return Response::redirect('/{module}/{entity}/list');
   }
   ```
5. **Validate enums** — filter GET/POST values against the Object's constants before using them:
   ```php
   $status = in_array($_GET['status'] ?? '', array_merge([''], Entity::STATUSES), true)
       ? ($_GET['status'] ?? '') : '';
   ```

---

## view.php Rules

1. **No business logic** — no DB calls, no Object method calls, no session access
2. **Always escape output** — define the `$e` helper at the top and use it on every value:
   ```php
   $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
   ```
3. **No PHP `echo` for raw user data** — always `<?= $e($value) ?>`, never `<?= $value ?>`
4. **No `include` or `require`** — views are included by the router; they do not include other files
5. **`$pageTitle` is optional** — set it at the top of the view if the default is wrong:
   ```php
   $pageTitle = 'Tickets';
   ```

---

## Query String Conventions

List pages preserve all active filters through sort links and pagination links using a `$qs` helper built in the view:

```php
$qs = fn(array $overrides) => '?' . http_build_query(array_filter(
    array_merge(['search' => $search, 'sort' => $sort, 'dir' => $dir, 'status' => $status, 'page' => $currentPage],
    $overrides),
    fn($v) => $v !== '' && $v !== null
));
```

Always use `array_filter` to omit empty values from the query string.

---

## What Not to Do

- Do not register routes manually — add a folder and let the router discover it
- Do not call `header()` or `exit` in a controller — return a `Response` object
- Do not output HTML from a controller — put it in `view.php`
- Do not render a success view after a POST — always redirect (PRG)
- Do not add auth checks inside controllers — rely on `module.php` + `config.php`
- Do not access `$_SESSION` in a view
- Do not echo unsanitized user data in a view — always use `$e()`
