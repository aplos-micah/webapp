# SQL Governance

Standards for table design, naming, migrations, and queries in AplosSuite.

---

## Table Naming

Tables are named `{module}_{entity}` in lowercase snake_case. The module prefix prevents collisions as new modules are added.

| Module | Entity | Table name |
|--------|--------|------------|
| ITSM | Ticket | `itsm_tickets` |
| Assets | Asset | `assets` *(exception — "assets" is unambiguous as a standalone name)* |
| KB | Article | `kb_articles` |
| CRM | Account | `accounts` |
| Admin | — | `users`, `user_module_access`, `company` |

Prefer the module prefix for all new tables. The CRM and Admin tables predate this convention; do not rename them.

---

## Required Columns

Every table **must** have these columns, in this order, with these exact definitions:

```sql
id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
-- ... all other columns ...
created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (id)
```

| Column | Type | Rule |
|--------|------|------|
| `id` | `INT UNSIGNED NOT NULL AUTO_INCREMENT` | First column, always |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` | Second-to-last column |
| `updated_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | Last column before PRIMARY KEY |

Do not use `TIMESTAMP` — use `DATETIME`. Do not use `BIGINT` for IDs unless a table is expected to exceed 4 billion rows.

---

## Column Conventions

### Nullable vs NOT NULL

- Columns that always have a value: `NOT NULL`
- Optional fields: `NULL DEFAULT NULL` — never just `NULL` without an explicit DEFAULT
- Boolean flags: `TINYINT(1) NOT NULL DEFAULT 0`

### String lengths

| Use | Type |
|-----|------|
| Names, titles, labels | `VARCHAR(255)` |
| Short codes, tags, slugs | `VARCHAR(100)` or `VARCHAR(50)` |
| Email addresses | `VARCHAR(255)` |
| Long text, notes, descriptions | `TEXT` |
| Comma-separated tag lists | `VARCHAR(500)` |

### ENUM columns

Use `ENUM` for fixed, known value sets that change rarely. List values in logical order (e.g. workflow order, not alphabetical). Always define a `DEFAULT`.

```sql
status ENUM('Draft','Published','Archived') NOT NULL DEFAULT 'Draft',
```

### Foreign keys

FK columns referencing `users.id`:
```sql
assigned_to INT UNSIGNED NULL DEFAULT NULL,
owner_id    INT UNSIGNED NULL DEFAULT NULL,
```

Always `NULL DEFAULT NULL` for FKs — never `NOT NULL` unless the record cannot exist without the parent.

---

## Engine, Charset, Collation

Every table must declare:

```sql
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

- `InnoDB` — required for foreign key constraints
- `utf8mb4` — full Unicode including emoji
- `utf8mb4_unicode_ci` — case-insensitive collation

---

## Index Naming

| Type | Pattern | Example |
|------|---------|---------|
| Regular index | `idx_{table}_{column}` | `idx_itsm_tickets_status` |
| Unique index | `uniq_{table}_{column}` | `uniq_users_email` |
| FK constraint | `fk_{table}_{column}` | `fk_assets_assigned` |

Always add an index on:
- Every FK column
- Every column used in `WHERE` filters on list pages (status, type, category)
- Every column used in `ORDER BY` on large tables

```sql
KEY idx_assets_status   (status),
KEY idx_assets_type     (type),
KEY idx_assets_assigned (assigned_to),
CONSTRAINT fk_assets_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
```

### FK `ON DELETE` actions

| Relationship | Action |
|-------------|--------|
| FK to `users` (assigned_to, owner_id, author_id) | `ON DELETE SET NULL` |
| FK to a parent record that owns children | `ON DELETE CASCADE` — only if orphaned children are meaningless |
| Any FK where orphaned data must be retained | `ON DELETE SET NULL` or `ON DELETE RESTRICT` |

Never use `ON DELETE CASCADE` on user references. Deleting a user must not silently delete their tickets, assets, or articles.

---

## Auto-Assigned Identifiers

Some entities get a human-readable identifier assigned after INSERT using the auto-increment ID. This requires a two-step create:

```sql
-- Step 1: INSERT without the identifier column
INSERT INTO itsm_tickets (title, …) VALUES (…)

-- Step 2: UPDATE to set the identifier using the new ID
UPDATE itsm_tickets
   SET ticket_number = CONCAT('TKT-', LPAD(id, 6, '0'))
 WHERE id = ?
```

| Module | Column | Format |
|--------|--------|--------|
| ITSM | `ticket_number` | `TKT-000001` |
| Assets | `asset_tag` | `ASSET-000001` |

The column must be defined as `VARCHAR(50) NOT NULL DEFAULT ''` so the INSERT succeeds before the UPDATE runs.

---

## Migration Files

### Location

```
Application/Module/{Module}/SQL/InterimUpdates/YYYYMMDD_{description}.sql
```

Platform-level (non-module) schema changes go to:
```
Application/Module/Admin/SQL/InterimUpdates/YYYYMMDD_{description}.sql
```

### Naming

```
20260504_itsm_tickets.sql       — create table
20260504_assets.sql             — create table
20260510_itsm_add_asset_id.sql  — alter table (add column)
```

- Date prefix: `YYYYMMDD`
- Lowercase snake_case description
- One logical change per file

### Content rules

1. Always use `CREATE TABLE IF NOT EXISTS` — never bare `CREATE TABLE`
2. Always use `ALTER TABLE … ADD COLUMN IF NOT EXISTS` — never bare `ADD COLUMN`
3. One file = one logical change. Do not combine unrelated table changes.
4. Include a comment on line 1 describing the change:

```sql
-- 20260504 — Create itsm_tickets table (ITSM module initial build)
```

5. Never include `DROP TABLE`, `TRUNCATE`, or `DELETE` in a migration file
6. Never include seed data in migration files

### Running migrations

Migrations are run through the Admin module at `/admin/migrations`. The `MigrationRunner` object discovers all `SQL/InterimUpdates/` folders across every module, tracks applied files in the `migrations` table, and prevents re-running.

---

## base.sql

Every module includes a `SQL/base.sql` that contains the complete `CREATE TABLE IF NOT EXISTS` statement for all tables owned by that module. This is the reference schema for fresh installations — it is not run by the MigrationRunner (which only processes `InterimUpdates/`).

`base.sql` must always reflect the current schema, including all columns added by subsequent migrations.

---

## Query Standards (in Object classes)

### Parameterised queries only

Never concatenate user input into a SQL string. All variable values go through prepared statement parameters:

```php
// Correct
$this->db->query('SELECT * FROM assets WHERE status = ?', [$status]);

// Wrong — SQL injection risk
$this->db->query("SELECT * FROM assets WHERE status = '{$status}'");
```

### Sort column whitelisting

Never interpolate a sort column directly from user input. Validate against the `SORTABLE` constant first:

```php
$col = in_array($sort, self::SORTABLE, true) ? $sort : 'created_at';
$dir = strtolower($dir) === 'asc' ? 'ASC' : 'DESC';
// Only $col and $dir are interpolated — they are whitelist-validated, not user strings
$sql = "SELECT … ORDER BY {$col} {$dir} LIMIT ? OFFSET ?";
```

### Pagination

Always paginate list queries. Default page size: `20`. Maximum enforced in API: `100`.

```php
$limit  = min(100, max(1, (int) ($_GET['limit']  ?? 20)));
$offset = max(0,           (int) ($_GET['offset'] ?? 0));
```

### NULL handling

Empty string from a form field should be stored as `NULL`, not `''`:

```php
$values[$field] = ($raw === '') ? null : $raw;
```

---

## What Not to Do

- Do not use `MyISAM` — use `InnoDB`
- Do not use `TIMESTAMP` columns — use `DATETIME`
- Do not use `utf8` charset — use `utf8mb4`
- Do not create a table without `created_at` and `updated_at`
- Do not name an index without following the `idx_` / `fk_` convention
- Do not write a migration that drops a column or table without explicit approval
- Do not concatenate user input into SQL — always use parameterised queries
- Do not interpolate sort columns without whitelisting against `SORTABLE`
- Do not put seed data in migration files
