# AplosCRM — Architecture Needs

## Completed

| # | Change | Notes |
|---|---|---|
| 1 | `.env` configuration | Credentials moved out of `config.php` into `configuration/.env` (gitignored). `Config` class reads via `getenv()`. `.env.example` committed as the contract. |
| 2 | Centralized logging | Custom `Logger` class writes JSON-lines to `storage/logs/app.log`. Instrumented at bootstrap, DB transactions, auth failures, router 404s, and authorization denials. Admin log viewer at `/admin/logviewer` with pagination, level filtering, archive, and delete. |
| 3 | Validation layer separation | `Validator` class at `Application/Validator.php` with stateless helpers: `required`, `email`, `minLength`, `enum`, `boolean`, `nullableInt`, `nullableFloat`. All 7 Object classes delegate in-memory validation to `Validator`. DB-dependent checks remain in objects until PHPUnit (#10) makes them independently testable. |
| 4 | Dependency injection container | `Container` class at `Application/Container.php`. Single shared `DB` instance per request. 14 services registered (`user`, `account`, `contact`, `location`, `opportunity`, `line_item`, `product`, plus 7 widget classes). All 21 controllers migrated from `new Foo(new DB())` to `Container::get('name')` / `Container::db()`. Per-controller `require_once` calls for Object and Widget files removed. |
| 5 | JSON API endpoints | `api/` routing track in router dispatches to `Application/Api/` controllers, skipping view and template pipeline. Session auth enforced at router level for all API routes. Four read endpoints: `GET /api/accounts`, `GET /api/contacts`, `GET /api/opportunities`, `GET /api/products`. All support `?id=`, `?search=`, `?limit=`, `?offset=`. Opportunities single-record response includes line items. |
| 7 | DB migration runner | `Runner` class tracks applied migrations in a `migrations` DB table. Files live in `Application/sql/interimUpdates/` named `YYYYMMDD_description.sql`. Admin UI at `/admin/migrations`. CLI via `php migrate.php`. |
| 8 | Mailer abstraction | `Mailer` interface with `PhpMailer` (PHP `mail()`) and `NullMailer` (logs only) implementations. `MailerFactory::make()` selects based on `MAIL_DRIVER` env var. Ready for password reset and notification emails. |
| 9 | HTTP Response objects | `Response` class at `Application/Response.php` with `redirect()` and `json()` factory methods and `send()`. Router captures controller return value and calls `send()` if it's a `Response`. All `header()` + `exit` pairs replaced with `return Response::redirect()` / `return Response::json()` across 21 controllers. |

---

## Pending

### Next

| # | Change | Why | Depends On |
|---|---|---|---|
| 6 | **htmx frontend interactivity** | Every CRM action requires a full page reload. htmx adds partial updates with no JS build pipeline, fitting the PHP-rendered model. | ~~#5~~ ✓ |
| 10 | **PHPUnit test foundation** | No tests. Refactors carry silent regression risk. Start with `Validator`, Object classes, and auth flow. | ~~#9~~ ✓ |
| 11 | **MCP server layer** | Enables Claude to query CRM data, read logs, and generate summaries conversationally. Thin layer over the JSON API. | ~~#5~~ ✓ |

---

## Recommended Order

Items 6, 10, and 11 are all unblocked and can be started in any order or in parallel.

---

## What Not to Change

- The front controller + router pattern — it is sound, keep it
- The module system — well structured, extend it
- The bcrypt password handling and parameterized queries — correct, leave them
- The `.htaccess` security hardening — thorough, maintain it
- No need for a heavy framework (Laravel, Symfony) — the custom architecture is the right scale for this app
