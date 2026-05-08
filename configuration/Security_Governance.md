# Security Governance

Input handling, output escaping, session security, and access control standards for AplosSuite.

---

## Defence in Depth

Security is applied at every layer. No single check is relied upon alone.

| Layer | Responsibility |
|-------|---------------|
| Router | Auth gates — unauthenticated and unauthorised requests never reach a controller |
| `Sanitization` | Strips HTML entities from all `$_GET` and `$_POST` input on every request |
| `Validator` | Validates and coerces individual field values before use |
| Object classes | Parameterised queries only — no user input is ever interpolated into SQL |
| Views | `htmlspecialchars()` on every output value — no raw user data ever reaches the browser |

---

## Input Sanitization

`Sanitization::sanitizeAll()` is called once at bootstrap (in `utilities.php`) before any controller runs. It strips HTML entities from all `$_GET` and `$_POST` values.

**Do not call `sanitizeAll()` again inside a controller.** It has already run.

For individual field values, use the `Validator` helpers:

```php
$name       = trim($_POST['name']   ?? '');              // string, trimmed
$assignedTo = Validator::nullableInt($_POST['assigned_to'] ?? '');  // int or null
$cost       = Validator::nullableFloat($_POST['cost']    ?? '');     // float or null
$isActive   = Validator::boolean($_POST['is_active']     ?? '');     // 1 or 0
$status     = Validator::enum($_POST['status'] ?? '', Entity::STATUSES, 'Active'); // validated enum
```

### Rules

- Always `trim()` string values before validation or storage
- Use `Validator::nullableInt()` for all FK columns — never `(int) $_POST['field']` when the value may be empty (that produces `0`, not `NULL`)
- Use `Validator::nullableFloat()` for decimal/money columns
- Use `Validator::enum()` when storing a value that must come from a fixed set
- Empty string from a form field must be stored as `NULL` in the database, not `''`

---

## Output Escaping

Every user-supplied value that appears in HTML output **must** be passed through `htmlspecialchars()`. Define the `$e` helper at the top of every view:

```php
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
```

Then use it consistently:

```php
// Correct
<?= $e($record['name']) ?>
<input value="<?= $e($record['title']) ?>">

// Wrong — XSS risk
<?= $record['name'] ?>
<input value="<?= $record['title'] ?>">
```

### Exceptions that still require care

| Context | What to do |
|---------|-----------|
| URLs in `href`/`action` | Use `$e()` on the full URL — never output raw query-string values |
| `nl2br()` output | Apply `$e()` first, then `nl2br()`: `nl2br($e($value))` |
| Integer IDs in URLs | Cast to `(int)` — no escaping needed: `?id=<?= (int) $record['id'] ?>` |
| JSON in `data-` attributes | Use `json_encode()` with `JSON_HEX_TAG \| JSON_HEX_APOS \| JSON_HEX_AMP` |

---

## SQL Injection Prevention

All database queries use parameterised statements through the `DB` class. User input is **never** interpolated into SQL strings.

```php
// Correct — parameterised
$this->db->query('SELECT * FROM assets WHERE status = ? AND type = ?', [$status, $type]);

// Wrong — SQL injection risk
$this->db->query("SELECT * FROM assets WHERE status = '{$status}'");
```

### Sort columns — whitelist validation

Column names cannot be parameterised (they are SQL identifiers, not values). Always whitelist against the Object's `SORTABLE` constant before interpolating:

```php
$col = in_array($sort, self::SORTABLE, true) ? $sort : 'created_at';
$dir = strtolower($dir) === 'asc' ? 'ASC' : 'DESC';
// $col and $dir are safe to interpolate — they come from a whitelist, not user input
$sql = "SELECT … ORDER BY {$col} {$dir} LIMIT ? OFFSET ?";
```

---

## Authentication and Session

### Session start

Call `session_start()` defensively at the top of any controller that reads `$_SESSION`:

```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

Never assume the session is already started.

### Reading session values

Always use the null-coalescing operator — never assume a session key exists:

```php
$userId   = (int) ($_SESSION['user_id']   ?? 0);
$userType = $_SESSION['user_type']         ?? '';
$module   = $_SESSION['module_itsm']       ?? null;
```

### Writing to session

Only write to `$_SESSION` for:
- Flash messages (`$_SESSION['_flash']`)
- After successful authentication (handled by `User::authenticate()`)

Do not write module access or user type directly — those are set by the auth system and must not be modified by controllers.

### Session fixation

`session_regenerate_id(true)` is called on login. Do not call it elsewhere.

---

## Access Control

### Router-level gates (automatic)

The router enforces three access rules automatically using `module.php`:

| Key | Behaviour |
|-----|-----------|
| `requiresLogin: true` | Redirect to `/login` if no valid session |
| `requiresUserType: 'Admin'` | Redirect to `/login` if user type does not match |
| `requiresModule{Name}: true` | Redirect to `/login` if `$_SESSION['module_{name}']` is empty |

Controllers do not need to repeat these checks.

### In-page permission checks

Some pages allow different behaviour based on who is viewing (e.g. KB articles: only the author or an admin can edit). Check this in the controller and pass a boolean to the view:

```php
$canEdit = $currentUser > 0 && (
    (int) $article['author_id'] === $currentUser
    || ($_SESSION['user_type'] ?? '') === 'Admin'
);
```

The view uses `$canEdit` to show or hide the edit form. It does not re-check session values.

### API access

API endpoints (`/api_v2/*`) inherit module access rules from `module.php`. The router validates session and module entitlement before the controller runs. No additional auth code is needed inside an API controller.

MCP tool access is validated by the `Mcp_v2/controller.php` dispatcher using the OAuth token's `module_tiers` map. Individual MCP tool handlers do not check auth.

---

## Sensitive Data

- Passwords are hashed using `password_hash()` with `PASSWORD_BCRYPT` — never store plaintext
- OAuth tokens are stored as hashed values — `hash('sha256', $token)`
- Session tokens are managed by PHP's session handler — never log or expose `session_id()`
- Do not log passwords, tokens, or full request bodies containing credentials
- The `.env` file contains secrets — it must never be committed to version control or served by the web server

---

## What Not to Do

- Do not interpolate `$_GET` or `$_POST` values directly into SQL
- Do not echo user data without `$e()` / `htmlspecialchars()`
- Do not call `(int) $_POST['fk_field']` when the field may be empty — use `Validator::nullableInt()`
- Do not store empty-string `''` in nullable FK or optional columns — store `NULL`
- Do not check `isLoggedIn()` inside a controller — rely on the router's auth gate
- Do not put auth logic in a view
- Do not expose stack traces or internal errors to the browser in production
- Do not commit `.env` to version control
