# AplosCRM — Architecture Needs

## Completed

| # | Change | Notes |
|---|---|---|
| 1 | `.env` configuration | Credentials moved out of `config.php` into `configuration/.env` (gitignored). `Config` class reads via `getenv()`. `.env.example` committed as the contract. |
| 2 | Centralized logging | Custom `Logger` class writes JSON-lines to `storage/logs/app.log`. Instrumented at bootstrap, DB transactions, auth failures, router 404s, and authorization denials. Admin log viewer at `/admin/logviewer` with pagination, level filtering, archive, and delete. |
| 7 | DB migration runner | `Runner` class tracks applied migrations in a `migrations` DB table. Files live in `Application/sql/interimUpdates/` named `YYYYMMDD_description.sql`. Admin UI at `/admin/migrations`. CLI via `php migrate.php`. |
| 8 | Mailer abstraction | `Mailer` interface with `PhpMailer` (PHP `mail()`) and `NullMailer` (logs only) implementations. `MailerFactory::make()` selects based on `MAIL_DRIVER` env var. Ready for password reset and notification emails. |

---

## Pending

### Next: Foundation (do in order)

| # | Change | Why | Depends On |
|---|---|---|---|
| 3 | **Validation layer separation** | Validation is mixed into Object classes alongside DB writes. Can't validate without hitting the DB. Prerequisite for clean API endpoints. | — |
| 4 | **Dependency injection container** | Objects instantiated inline everywhere (`new Account(new DB())`). Tightly coupled, untestable. | — |
| 9 | **HTTP Response objects** | Raw `header()` calls scattered through controllers. Untestable. Pairs with #4 to make controllers fully testable. | #4 |

### Then: Features

| # | Change | Why | Depends On |
|---|---|---|---|
| 5 | **JSON API endpoints** | All interactions are full-page reloads. No AJAX possible. Required for htmx and MCP. | #3 |
| 6 | **htmx frontend interactivity** | Every CRM action requires a full page reload. htmx adds partial updates with no JS build pipeline, fitting the PHP-rendered model. | #5 |

### Then: Quality & Integration

| # | Change | Why | Depends On |
|---|---|---|---|
| 10 | **PHPUnit test foundation** | No tests. Refactors carry silent regression risk. Start with auth and Object classes. | #4, #9 |
| 11 | **MCP server layer** | Enables Claude to query CRM data, read logs, and generate summaries conversationally. Best built as a thin layer over the JSON API. | #5 |

---

## Recommended Order

```
3 → 4 → 9 → 5 → 6
                 → 10
                 → 11
```

Items 3 and 4 are fully independent and can be started in parallel.
Items 10 and 11 can both begin once 5 is complete.

---

## What Not to Change

- The front controller + router pattern — it is sound, keep it
- The module system — well structured, extend it
- The bcrypt password handling and parameterized queries — correct, leave them
- The `.htaccess` security hardening — thorough, maintain it
- No need for a heavy framework (Laravel, Symfony) — the custom architecture is the right scale for this app
