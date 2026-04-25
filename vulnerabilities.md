# Vulnerability Scan — public_html

**Scope:** `/public_html` directory only  
**Files analysed:** `index.php`, `.htaccess`, `simplifying.html`, `robots.txt`, `assets/vendor/htmx.min.js`  
**Date:** 2026-04-25

---

## Summary

| Severity | Count |
|---|---|
| HIGH | 3 |
| MEDIUM | 5 |
| LOW | 7 |

---

## HIGH

### H1 — Content-Security-Policy allows `unsafe-inline` scripts
**File:** `public_html/.htaccess` line 23  
```
script-src 'self' 'unsafe-inline'
```
Permitting `unsafe-inline` in `script-src` allows any inline `<script>` block or event-handler attribute (`onclick`, `onload`, etc.) to execute. This defeats the primary purpose of CSP as an XSS mitigation. If an attacker manages to inject HTML anywhere in a page, inline scripts will execute without restriction.

**Fix:** Remove `'unsafe-inline'` from `script-src`. Use a nonce or hash-based approach for any legitimate inline scripts. If no inline scripts exist in the application, `script-src 'self'` alone is sufficient.

---

### H2 — PHP hardening directives ineffective under CGI/FastCGI
**File:** `public_html/.htaccess` lines 140–148  
```apache
<IfModule mod_php.c>
    php_flag display_errors Off
    php_flag expose_php Off
    php_flag allow_url_fopen Off
    php_flag allow_url_include Off
    ...
</IfModule>
```
The server runs PHP via `ea-php81` (cPanel EasyApache 4), which executes as a CGI/FastCGI handler — not as `mod_php`. The `<IfModule mod_php.c>` block is therefore **never loaded**, meaning `display_errors`, `expose_php`, `allow_url_fopen`, and `allow_url_include` are controlled only by the server's `php.ini` defaults and may be `On`.

**Fix:** Set these directives in the server's `php.ini` or a `php.ini` file in the application root, or use a `.user.ini` file which is honoured by PHP-FPM/FastCGI:
```ini
display_errors = Off
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
```

---

### H3 — htmx 1.9.12 ships with `allowEval: true` by default
**File:** `public_html/assets/vendor/htmx.min.js`  
htmx version **1.9.12** is bundled. Its default configuration includes `allowEval: true` and `allowScriptTags: true`, meaning htmx will evaluate JavaScript found in AJAX-swapped server responses and process `<script>` tags. If any endpoint returns user-influenced HTML without strict output encoding, this creates a stored/reflected XSS vector via htmx swap operations.

**Fix (immediate):** Disable eval and script tag processing at application startup:
```js
htmx.config.allowEval = false;
htmx.config.allowScriptTags = false;
```
**Fix (long-term):** Upgrade to htmx 2.x, which removes `allowEval` entirely and has a more restrictive default security model.

---

## MEDIUM

### M1 — `Strict-Transport-Security` (HSTS) header absent
**File:** `public_html/.htaccess`  
No `Strict-Transport-Security` header is set. Without HSTS, browsers will accept HTTP connections, enabling SSL stripping attacks — an attacker on the network can downgrade a user's HTTPS connection to HTTP and intercept or modify traffic.

**Fix:**
```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

---

### M2 — `PUT` and `DELETE` HTTP methods unnecessarily permitted
**File:** `public_html/.htaccess` line 104  
```apache
<LimitExcept GET POST PUT DELETE OPTIONS>
    Require all denied
</LimitExcept>
```
The application only uses `GET` and `POST`. Allowing `PUT`, `DELETE`, and `OPTIONS` expands the attack surface without benefit — certain server-side configurations may handle these methods in unintended ways.

**Fix:**
```apache
<LimitExcept GET POST>
    Require all denied
</LimitExcept>
```

---

### M3 — Attack pattern filters only cover query strings, not request bodies
**File:** `public_html/.htaccess` lines 63–85  
The SQL injection, XSS, and path traversal blocking rules inspect `%{QUERY_STRING}` only. POST-body payloads carrying the same patterns pass through unrestricted at the Apache layer. Protections rely entirely on application-level sanitisation.

**Note:** The application does apply `htmlspecialchars` via `Sanitization::sanitizeAll()`, and uses parameterised queries throughout. This mitigates the risk, but defence-in-depth at the Apache layer is incomplete.

**Fix:** Add parallel `%{THE_REQUEST}` or mod_security rules covering POST bodies, or document explicitly that application-layer sanitisation is the intended control.

---

### M4 — `style-src 'unsafe-inline'` permits CSS injection
**File:** `public_html/.htaccess` line 23  
```
style-src 'self' 'unsafe-inline'
```
Inline styles are permitted. CSS injection can be used for data exfiltration (e.g., CSS attribute selectors leaking form values to an attacker-controlled server) and UI redressing.

**Fix:** Remove `'unsafe-inline'` from `style-src`. Move inline styles to external stylesheets. If inline styles are required in specific contexts, use a nonce.

---

### M5 — Deprecated `X-XSS-Protection` header
**File:** `public_html/.htaccess` line 21  
```
X-XSS-Protection: 1; mode=block
```
This header is deprecated and ignored by all modern browsers. In some legacy browsers (IE 8/9), the XSS auditor it activates has known bypass techniques and can itself introduce vulnerabilities. Its presence provides no protection and should be removed to avoid confusion.

**Fix:** Remove the `X-XSS-Protection` header entirely. Rely on the CSP for XSS mitigation.

---

## LOW

### L1 — Missing `Cross-Origin-Opener-Policy` header
**File:** `public_html/.htaccess`  
Without `Cross-Origin-Opener-Policy`, pages may share a browsing context group with cross-origin pages, enabling certain cross-origin information leak attacks (Spectre-class side channels, `window.opener` access).

**Fix:** `Header always set Cross-Origin-Opener-Policy "same-origin"`

---

### L2 — Missing `Cross-Origin-Resource-Policy` header
**File:** `public_html/.htaccess`  
Without `Cross-Origin-Resource-Policy`, resources in `public_html/assets/` can be loaded by any cross-origin page.

**Fix:** `Header always set Cross-Origin-Resource-Policy "same-site"`

---

### L3 — External font CDN in maintenance page leaks user IPs
**File:** `public_html/simplifying.html` line 7  
```html
<link href="https://fonts.googleapis.com/css2?family=...">
```
The maintenance page loads fonts from Google's CDN. Any user who hits the maintenance page has their IP address, browser, and timestamp logged by Google. This may conflict with privacy policies or regulations.

**Fix:** Self-host the fonts or use a `<link rel="preconnect">` with a privacy proxy, or use system font stacks for the maintenance page.

---

### L4 — Dead code in entry point
**File:** `public_html/index.php` lines 9–10  
```php
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
```
These variables are computed but never used. Routing is handled entirely within `utilities.php` → `Router::dispatch()`. The dead code is harmless but creates maintenance confusion.

**Fix:** Remove lines 9–10.

---

### L5 — User agent blocking is trivially bypassed
**File:** `public_html/.htaccess` lines 52–57  
The scanner/tool user agent blocklist can be bypassed by any attacker who simply changes their user agent string. This provides no meaningful security guarantee and should not be relied upon as a control.

**Note:** This is defence-in-depth against automated scanning, not malicious actors. Acceptable as a nuisance filter but should not be documented as a security control.

---

### L6 — `Options +FollowSymLinks` enabled
**File:** `public_html/.htaccess` line 9  
If any symlink within `public_html` points outside the webroot, Apache will follow it and serve those files. This is required for `mod_rewrite` but should be audited periodically.

**Fix:** Ensure no symlinks exist in `public_html` pointing to sensitive paths. Consider `Options +SymLinksIfOwnerMatch` as a more restrictive alternative if the server supports it.

---

### L7 — htmx not upgraded to current major version
**File:** `public_html/assets/vendor/htmx.min.js`  
htmx **1.9.12** is in use. The current major release is **2.x**, which removes `allowEval` entirely, tightens default security configuration, and includes cumulative security improvements. Version 1.x is in maintenance mode.

**Fix:** Evaluate upgrading to htmx 2.x. Review the migration guide for breaking changes before upgrading.

---

## Positive Controls Observed

| Control | Status |
|---|---|
| Directory listing disabled (`Options -Indexes`) | ✅ |
| Dotfiles blocked (`.DS_Store`, `.git`, etc.) | ✅ |
| Server signature hidden (`ServerSignature Off`) | ✅ |
| `X-Frame-Options: SAMEORIGIN` | ✅ |
| `X-Content-Type-Options: nosniff` | ✅ |
| `Referrer-Policy: strict-origin-when-cross-origin` | ✅ |
| `Permissions-Policy` restricting camera/mic/geo | ✅ |
| SQL/XSS/traversal pattern blocking (query strings) | ✅ |
| Sensitive file extensions blocked (`.sql`, `.log`, `.env`, etc.) | ✅ |
| `robots.txt` present with `Disallow: /` | ✅ |
| `X-Powered-By` header removed | ✅ |
