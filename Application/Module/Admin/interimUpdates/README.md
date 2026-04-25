# interimUpdates

This folder contains one-off migration scripts — `ALTER TABLE`, `UPDATE`, and data transformation statements — that patch existing databases as the schema evolves.

## Naming Convention

```
YYYYMMDD_Object.sql
```

- `YYYYMMDD` — the date the migration was written (e.g. `20260413`)
- `Object` — the table or feature being changed (e.g. `users`, `opportunity_stages`)
- Examples: `20260413_users.sql`, `20260413_opportunity_stages.sql`

## Rules

- **Never put `CREATE TABLE` statements here.** Those belong in the core schema files.
- Each script should include a comment header explaining what it does.
- Write scripts to be re-runnable where possible (e.g. `ADD COLUMN IF NOT EXISTS`, `MODIFY COLUMN`).
- After applying a migration, update the corresponding core schema file (`/application/sql/*.sql`) to reflect the new state.

## Execution Order

Apply scripts in ascending date order. Track which scripts have been applied using `applied.log`.

## applied.log

Log each script after it has been successfully run against an environment:

```
YYYYMMDD_Object.sql  | YYYY-MM-DD | environment (e.g. local, staging, production)
```
