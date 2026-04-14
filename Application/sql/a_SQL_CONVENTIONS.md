# AplosCRM — SQL Conventions

## Folder Structure

```
application/sql/
├── 1-users.sql                          # Core schema — run in prefix order
├── 2-accounts.sql
├── 3-contacts.sql
├── 3-locations.sql
├── 3-product_definitions.sql
├── 4-opportunities.sql
├── 5-opportunity_product_line_items.sql
├── 6-triggers_opportunity_amount.sql
├── a_SQL_CONVENTIONS.md                 # This file
├── SeedFiles/                           # Seed data scripts
│   └── seed_*.sql
└── interimUpdates/                      # One-off migration scripts
    ├── README.md
    ├── applied.log
    └── YYYYMMDD_Object.sql
```

---

## Rule 1 — Core Schema Files (`/application/sql/*.sql`)

- One file per table, named after the table with an execution-order prefix (e.g. `1-users.sql`, `3-contacts.sql`).
- The numeric prefix reflects the order files must be run on a fresh install, driven by foreign key dependencies. Files with the same prefix have no dependency on each other and can be run in any order.
- Each file contains a single `CREATE TABLE IF NOT EXISTS` statement representing the **current, complete** schema.
- The `CREATE TABLE` is the source of truth — it must reflect the final desired state so any fresh install is fully up to date with no additional steps.
- **No `ALTER TABLE`, `UPDATE`, or any other migration statements** belong in these files.
- Triggers live in their own dedicated files (e.g. `6-triggers_opportunity_amount.sql`) and are kept separate from schema files.

---

## Rule 2 — Seed Files (`/application/sql/SeedFiles/`)

- Seed files populate reference or test data for a fresh installation.
- They must be written against the **current core schema files** — never against an older or interim schema state.
- Name files descriptively: `seed_<table>.sql` (e.g. `seed_product_definitions.sql`).
- Seed files should use `INSERT IGNORE` or `INSERT ... ON DUPLICATE KEY UPDATE` so they are safe to re-run.

---

## Rule 3 — Interim Updates (`/application/sql/interimUpdates/`)

- Any `ALTER TABLE`, `UPDATE`, or other one-off migration script goes here — never in a core schema file.
- **Naming convention:** `YYYYMMDD_Object.sql`
  - `YYYYMMDD` — the date the migration was written (e.g. `20260413`)
  - `Object` — the table or feature being changed (e.g. `users`, `opportunity_stages`)
  - Examples: `20260413_users.sql`, `20260413_opportunity_stages.sql`
- Each file should include a comment header describing what it does and whether it is safe to re-run.
- After applying an interim update, **update the corresponding core schema file** to reflect the new state so the next fresh install picks it up automatically.
- Log each applied script in `interimUpdates/applied.log`.

---

## Workflow Summary

```
New column / schema change:
  1. Update the core N-table.sql file to reflect final state
  2. Write an interimUpdates/YYYYMMDD_Object.sql to patch existing databases
  3. Run the interim script against any existing environment
  4. Record the run in interimUpdates/applied.log

New seed data:
  1. Add or update the relevant SeedFiles/seed_*.sql
  2. Verify it runs cleanly against the current core schema

Never put ALTER or UPDATE statements in core schema files.
Never put CREATE TABLE statements in interim update files.
```
