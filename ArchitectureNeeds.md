# AplosCRM — Architecture Needs

## Completed

| # | Change | Notes |
|---|---|---|
| 1 | `.env` configuration | Credentials moved out of `config.php` into `configuration/.env` (gitignored). `Config` class reads via `getenv()`. `.env.example` committed as the contract. |
| 2 | Centralized logging | Custom `Logger` class writes JSON-lines to `storage/logs/app.log`. Instrumented at bootstrap, DB transactions, auth failures, router 404s, and authorization denials. Admin log viewer at `/admin/logviewer` with pagination, level filtering, archive, and delete. |
| 3 | Validation layer separation | `Validator` class at `Application/Validator.php` with stateless helpers: `required`, `email`, `minLength`, `enum`, `boolean`, `nullableInt`, `nullableFloat`. All 7 Object classes delegate in-memory validation to `Validator`. DB-dependent checks remain in objects until PHPUnit (#10) makes them independently testable. |
| 4 | Dependency injection container | `Container` class at `Application/Container.php`. Single shared `DB` instance per request. 14 services registered (`user`, `account`, `contact`, `location`, `opportunity`, `line_item`, `product`, plus 7 widget classes). All 21 controllers migrated from `new Foo(new DB())` to `Container::get('name')` / `Container::db()`. Per-controller `require_once` calls for Object and Widget files removed. |
| 7 | DB migration runner | `Runner` class tracks applied migrations in a `migrations` DB table. Files live in `Application/sql/interimUpdates/` named `YYYYMMDD_description.sql`. Admin UI at `/admin/migrations`. CLI via `php migrate.php`. |
| 8 | Mailer abstraction | `Mailer` interface with `PhpMailer` (PHP `mail()`) and `NullMailer` (logs only) implementations. `MailerFactory::make()` selects based on `MAIL_DRIVER` env var. Ready for password reset and notification emails. |

---

## Pending

### Next

| # | Change | Why | Depends On |
|---|---|---|---|
| 9 | **HTTP Response objects** | Raw `header()` calls scattered through controllers. Untestable. Prerequisite for clean PHPUnit controller tests. | — |
| 5 | **JSON API endpoints** | All interactions are full-page reloads. No AJAX possible. Required for htmx and MCP. | — |

### Then: Frontend

| # | Change | Why | Depends On |
|---|---|---|---|
| 6 | **htmx frontend interactivity** | Every CRM action requires a full page reload. htmx adds partial updates with no JS build pipeline, fitting the PHP-rendered model. | #5 |

### Then: Quality & Integration

| # | Change | Why | Depends On |
|---|---|---|---|
| 10 | **PHPUnit test foundation** | No tests. Refactors carry silent regression risk. Start with `Validator`, Object classes, and auth flow. | #9 |
| 11 | **MCP server layer** | Enables Claude to query CRM data, read logs, and generate summaries conversationally. Thin layer over the JSON API. | #5 |

---

## Recommended Order

```
9 ─┐
   ├─→ 10
5 ─┘
   └─→ 6
   └─→ 11
```

Items 9 and 5 are independent and can be done in parallel.
Items 10, 6, and 11 follow once their dependencies are complete.

---

## What Not to Change

- The front controller + router pattern — it is sound, keep it
- The module system — well structured, extend it
- The bcrypt password handling and parameterized queries — correct, leave them
- The `.htaccess` security hardening — thorough, maintain it
- No need for a heavy framework (Laravel, Symfony) — the custom architecture is the right scale for this app
